<?php

namespace App\Http\Controllers;

use App\Models\SupplyRequest;
use App\Services\SupplyRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRequestController extends Controller
{
    public function index(): View
    {
        $requests = SupplyRequest::with(['user.department', 'department'])
            ->where('status', 'admin_review')
            ->latest()
            ->paginate(15);

        return view('admin.requests-index', compact('requests'));
    }

    public function show(SupplyRequest $request): View
    {
        $request->load(['items.inventory', 'user', 'department', 'reviewer']);

        return view('admin.requests-show', ['supplyRequest' => $request]);
    }

    public function approve(SupplyRequest $request, SupplyRequestService $service): RedirectResponse
    {
        try {
            $request->load(['items.inventory', 'user']);
            $service->approve($request, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request approved and stock reserved.');
    }

    public function reject(Request $httpRequest, SupplyRequest $request, SupplyRequestService $service): RedirectResponse
    {
        $data = $httpRequest->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        try {
            $service->reject($request, auth()->user(), $data['rejection_reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request rejected.');
    }
}
