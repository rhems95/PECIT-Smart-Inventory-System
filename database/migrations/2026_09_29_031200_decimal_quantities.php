<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $this->modify('inventory', [
            'quantity' => 'DECIMAL(12,4) NOT NULL DEFAULT 0',
            'reserved_quantity' => 'DECIMAL(12,4) NOT NULL DEFAULT 0',
            'minimum_stock' => 'DECIMAL(12,4) NOT NULL DEFAULT 10',
        ]);
        $this->modify('inventory_size_stocks', [
            'quantity' => 'DECIMAL(12,4) NOT NULL DEFAULT 0',
            'reserved_quantity' => 'DECIMAL(12,4) NOT NULL DEFAULT 0',
        ]);
        $this->modify('request_items', [
            'quantity_requested' => 'DECIMAL(12,4) NOT NULL',
            'quantity_approved' => 'DECIMAL(12,4) NULL',
            'quantity_released' => 'DECIMAL(12,4) NOT NULL DEFAULT 0',
        ]);
        $this->modify('purchase_request_items', [
            'quantity' => 'DECIMAL(12,4) NOT NULL',
        ]);
        $this->modify('transactions', [
            'quantity' => 'DECIMAL(12,4) NOT NULL',
            'quantity_before' => 'DECIMAL(12,4) NOT NULL',
            'quantity_after' => 'DECIMAL(12,4) NOT NULL',
            'quantity_in' => 'DECIMAL(12,4) NOT NULL DEFAULT 0',
            'quantity_out' => 'DECIMAL(12,4) NOT NULL DEFAULT 0',
            'balance_after' => 'DECIMAL(12,4) NULL',
        ]);
        $this->modify('stock_logs', [
            'quantity' => 'DECIMAL(12,4) NOT NULL',
            'balance_after' => 'DECIMAL(12,4) NOT NULL',
        ]);
    }

    /**
     * @param  array<string, string>  $columns
     */
    protected function modify(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$definition}");
        }
    }
};
