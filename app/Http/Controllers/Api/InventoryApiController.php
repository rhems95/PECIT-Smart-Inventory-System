<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Inventory::with(['category']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('item_name', 'like', "%{$search}%");
        }

        return response()->json($query->paginate(20));
    }

    public function show(Inventory $inventory): JsonResponse
    {
        $inventory->load(['category']);

        return response()->json($inventory);
    }
}
