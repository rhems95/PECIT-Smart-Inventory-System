<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplyRequest;
use App\Services\SupplyRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplyRequestApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requests = SupplyRequest::with('items.inventory')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($requests);
    }

    public function store(Request $request, SupplyRequestService $service): JsonResponse
    {
        $data = $request->validate([
            'purpose' => ['required', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required', 'exists:inventory,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $supplyRequest = $service->create($request->user(), $data['items'], $data['purpose']);

        return response()->json($supplyRequest->load('items.inventory'), 201);
    }
}
