<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\SupplyRequest;
use App\Services\SupplyRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacultyRequestController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SupplyRequest::class);

        $requests = SupplyRequest::with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('requests.index', compact('requests'));
    }

    public function create(): View
    {
        $this->authorize('create', SupplyRequest::class);

        return view('requests.create', [
            'inventory' => Inventory::with('category')->orderBy('item_name')->get(),
        ]);
    }

    public function store(Request $request, SupplyRequestService $service): RedirectResponse
    {
        $this->authorize('create', SupplyRequest::class);

        $data = $request->validate([
            'purpose' => ['required', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required', 'exists:inventory,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $supplyRequest = $service->create(auth()->user(), $data['items'], $data['purpose']);

        return redirect()->route('requests.show', $supplyRequest)->with('success', 'Request submitted successfully.');
    }

    public function show(SupplyRequest $request): View
    {
        $this->authorize('view', $request);

        $request->load(['items.inventory', 'department', 'reviewer', 'approver', 'releaser']);

        return view('requests.show', ['supplyRequest' => $request]);
    }

    public function cancel(SupplyRequest $request, SupplyRequestService $service): RedirectResponse
    {
        $this->authorize('cancel', $request);

        try {
            $service->cancel($request, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request cancelled.');
    }
}
