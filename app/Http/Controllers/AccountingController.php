<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\SupplyRequest;
use App\Services\PurchaseRequestService;
use App\Services\SupplyRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function requests(): View
    {
        $requests = SupplyRequest::with(['user.department', 'department'])
            ->whereIn('status', ['pending', 'accounting_review'])
            ->latest()
            ->paginate(15);

        return view('accounting.requests-index', compact('requests'));
    }

    public function showRequest(SupplyRequest $request): View
    {
        $request->load(['items.inventory', 'user.department', 'department']);

        return view('accounting.requests-show', [
            'supplyRequest' => $request,
            'budget' => app(\App\Services\FacultyBudgetService::class)->snapshot(
                $request->department ?? $request->user?->department,
                $request->id,
            ),
        ]);
    }

    public function reviewRequest(Request $httpRequest, SupplyRequest $request, SupplyRequestService $service): RedirectResponse
    {
        $data = $httpRequest->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:request_items,id'],
            'items.*.quantity_approved' => ['required', 'integer', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $service->accountingReview($request, auth()->user(), $data['items']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('accounting.requests')->with('success', 'Request forwarded to administrator.');
    }

    public function payments(): View
    {
        $purchases = PurchaseRequest::with('user')
            ->where('status', 'payment_submitted')
            ->latest()
            ->paginate(15);

        $recentVerifiedPayments = PurchaseRequest::query()->recentlyVerified(10)->get();

        return view('accounting.payments-index', compact('purchases', 'recentVerifiedPayments'));
    }

    public function showPayment(PurchaseRequest $purchase): View
    {
        $purchase->load(['items.inventory', 'user', 'payments']);

        return view('accounting.payments-show', compact('purchase'));
    }

    public function verifyPayment(PurchaseRequest $purchase, PurchaseRequestService $service): RedirectResponse
    {
        try {
            $purchase->load(['items.inventory', 'user', 'payments']);
            $service->verifyPayment($purchase, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('accounting.payments')->with('success', 'Payment verified.');
    }
}
