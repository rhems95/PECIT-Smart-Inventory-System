<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $purchases = PurchaseRequest::with(['items', 'payments'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    public function checkout(PurchaseRequestService $service): RedirectResponse
    {
        $this->authorize('checkout', PurchaseRequest::class);

        try {
            $cart = session('cart', []);
            $purchase = $service->checkout(auth()->user(), $cart);
            session()->forget('cart');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Checkout complete. Please proceed with payment.');
    }

    public function show(PurchaseRequest $purchase): View
    {
        $this->authorize('view', $purchase);
        $purchase->load(['items.inventory', 'payments']);

        return view('purchases.show', compact('purchase'));
    }

    public function paymentSlip(PurchaseRequest $purchase)
    {
        $this->authorize('downloadPaymentSlip', $purchase);
        $purchase->load(['items.inventory', 'payments', 'user']);

        $pdf = Pdf::loadView('purchases.payment-slip-pdf', compact('purchase'));

        return $pdf->download("payment-slip-{$purchase->purchase_number}.pdf");
    }

    public function uploadReceipt(Request $request, PurchaseRequest $purchase): RedirectResponse
    {
        $this->authorize('uploadReceipt', $purchase);

        $data = $request->validate([
            'receipt' => [
                'required',
                'file',
                'max:5120',
                'mimetypes:image/jpeg,image/png,image/jpg,image/webp,application/pdf',
            ],
        ], [
            'receipt.required' => 'Please choose a receipt file to upload.',
            'receipt.mimetypes' => 'Receipt must be a JPG, PNG, WEBP, or PDF file.',
            'receipt.max' => 'Receipt must be 5MB or smaller.',
        ]);

        $payment = $purchase->payments()->latest()->first();
        if (! $payment) {
            return back()->with('error', 'No payment record found for this purchase. Please contact Accounting.');
        }

        try {
            $path = $data['receipt']->store('receipts', 'public');

            if (! $path) {
                return back()->with('error', 'Could not save the receipt file. Please try again.');
            }

            $payment->update(['receipt_path' => $path]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Receipt upload failed. Please try again or use a JPG/PNG/PDF under 5MB.');
        }

        return back()->with('success', 'Payment receipt uploaded successfully.');
    }

    public function showReceipt(PurchaseRequest $purchase): StreamedResponse
    {
        $this->authorize('view', $purchase);

        $path = $purchase->payments()->latest()->value('receipt_path');

        if (! is_string($path) || $path === '' || str_contains($path, '..') || ! Storage::disk('public')->exists($path)) {
            abort(404, 'Receipt file not found.');
        }

        return Storage::disk('public')->response($path);
    }

    public function cancel(PurchaseRequest $purchase, PurchaseRequestService $service): RedirectResponse
    {
        $this->authorize('cancel', $purchase);

        try {
            $service->cancel($purchase, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase cancelled.');
    }
}
