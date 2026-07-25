<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $purchases = PurchaseRequest::with('items')
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
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $data['receipt']->store('receipts', 'public');

        $purchase->payments()->latest()->first()?->update(['receipt_path' => $path]);

        return back()->with('success', 'Payment receipt uploaded successfully.');
    }
}
