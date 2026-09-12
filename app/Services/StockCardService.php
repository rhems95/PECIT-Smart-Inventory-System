<?php

namespace App\Services;

use App\Enums\InventoryTransactionType;
use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class StockCardService
{
    /**
     * @return LengthAwarePaginator<int, Transaction>
     */
    public function paginate(Inventory $inventory, Request $request): LengthAwarePaginator
    {
        $query = Transaction::query()
            ->with(['performer', 'supplier'])
            ->where('inventory_id', $inventory->id)
            ->physical();

        if ($from = $request->date('from')) {
            $query->whereRaw('DATE(COALESCE(transaction_date, created_at)) >= ?', [$from->toDateString()]);
        }

        if ($to = $request->date('to')) {
            $query->whereRaw('DATE(COALESCE(transaction_date, created_at)) <= ?', [$to->toDateString()]);
        }

        if ($type = $request->string('type')->trim()->toString()) {
            $enum = InventoryTransactionType::tryFrom($type);
            if ($enum && $enum->isPhysical()) {
                $query->where('type', $enum->value);
            }
        }

        if ($supplierId = $request->integer('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($reference = $request->string('reference')->trim()->toString()) {
            $query->where(function ($q) use ($reference) {
                $q->where('reference_number', 'like', "%{$reference}%")
                    ->orWhere('delivery_receipt_number', 'like', "%{$reference}%")
                    ->orWhere('transaction_number', 'like', "%{$reference}%");
            });
        }

        return $query
            ->orderByRaw('COALESCE(transaction_date, created_at) asc')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();
    }
}
