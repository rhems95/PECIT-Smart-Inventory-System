<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units_of_measurement', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol', 20)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code', 30)->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inventory_price_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->decimal('old_unit_price', 12, 2);
            $table->decimal('new_unit_price', 12, 2);
            $table->string('reason')->nullable();
            $table->foreignId('adjusted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('adjusted_at');
            $table->timestamps();
        });

        if (Schema::hasTable('inventory') && ! Schema::hasColumn('inventory', 'unit_of_measurement_id')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->foreignId('unit_of_measurement_id')
                    ->nullable()
                    ->constrained('units_of_measurement')
                    ->nullOnDelete();
            });
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `transactions` MODIFY `type` VARCHAR(40) NOT NULL');
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'source_type')) {
                $table->string('source_type', 40)->nullable();
            }
            if (! Schema::hasColumn('transactions', 'quantity_in')) {
                $table->decimal('quantity_in', 12, 4)->default(0);
            }
            if (! Schema::hasColumn('transactions', 'quantity_out')) {
                $table->decimal('quantity_out', 12, 4)->default(0);
            }
            if (! Schema::hasColumn('transactions', 'balance_after')) {
                $table->decimal('balance_after', 12, 4)->nullable();
            }
            if (! Schema::hasColumn('transactions', 'unit_cost')) {
                $table->decimal('unit_cost', 12, 2)->nullable();
            }
            if (! Schema::hasColumn('transactions', 'total_cost')) {
                $table->decimal('total_cost', 12, 2)->nullable();
            }
            if (! Schema::hasColumn('transactions', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            }
            if (! Schema::hasColumn('transactions', 'reference_number')) {
                $table->string('reference_number')->nullable();
            }
            if (! Schema::hasColumn('transactions', 'delivery_receipt_number')) {
                $table->string('delivery_receipt_number')->nullable();
            }
            if (! Schema::hasColumn('transactions', 'size')) {
                $table->string('size', 10)->nullable();
            }
            if (! Schema::hasColumn('transactions', 'transaction_date')) {
                $table->timestamp('transaction_date')->nullable();
            }
        });

        $this->seedUnitsAndBackfill();
        $this->backfillTransactions();
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }
            foreach ([
                'source_type', 'quantity_in', 'quantity_out', 'balance_after',
                'unit_cost', 'total_cost', 'reference_number', 'delivery_receipt_number',
                'size', 'transaction_date',
            ] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasTable('inventory') && Schema::hasColumn('inventory', 'unit_of_measurement_id')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->dropConstrainedForeignId('unit_of_measurement_id');
            });
        }

        Schema::dropIfExists('inventory_price_adjustments');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('units_of_measurement');
    }

    protected function seedUnitsAndBackfill(): void
    {
        $now = now();
        $units = [
            ['Piece', 'pcs', 'Individual pieces'],
            ['Box', 'box', 'Boxes'],
            ['Pack', 'pack', 'Packs'],
            ['Ream', 'ream', 'Paper reams'],
            ['Kilogram', 'kg', 'Kilograms'],
            ['Liter', 'L', 'Liters'],
            ['Unit', 'unit', 'Generic units'],
            ['Bottle', 'bottle', 'Bottles'],
            ['Case', 'case', 'Cases'],
            ['Set', 'set', 'Sets'],
        ];

        foreach ($units as [$name, $symbol, $description]) {
            $exists = DB::table('units_of_measurement')->where('symbol', $symbol)->exists();
            if (! $exists) {
                DB::table('units_of_measurement')->insert([
                    'name' => $name,
                    'symbol' => $symbol,
                    'description' => $description,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (! Schema::hasTable('inventory') || ! Schema::hasColumn('inventory', 'unit')) {
            return;
        }

        $rows = DB::table('inventory')->select('id', 'unit', 'unit_of_measurement_id')->get();
        foreach ($rows as $row) {
            if ($row->unit_of_measurement_id) {
                continue;
            }
            $symbol = strtolower(trim((string) $row->unit));
            if ($symbol === 'piece' || $symbol === 'pcs' || $symbol === 'pc') {
                $symbol = 'pcs';
            }
            $unitId = DB::table('units_of_measurement')->whereRaw('LOWER(symbol) = ?', [$symbol])->value('id');
            if (! $unitId && $row->unit) {
                $unitId = DB::table('units_of_measurement')->insertGetId([
                    'name' => ucfirst((string) $row->unit),
                    'symbol' => substr((string) $row->unit, 0, 20),
                    'description' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            if ($unitId) {
                DB::table('inventory')->where('id', $row->id)->update(['unit_of_measurement_id' => $unitId]);
            }
        }
    }

    protected function backfillTransactions(): void
    {
        if (! Schema::hasTable('transactions')) {
            return;
        }

        $inbound = ['stock_in', 'opening_balance', 'adjustment_in', 'purchase_delivery'];
        $nonPhysical = ['reserve', 'restore'];

        DB::table('transactions')->orderBy('id')->chunkById(200, function ($rows) use ($inbound, $nonPhysical) {
            foreach ($rows as $row) {
                $type = (string) $row->type;
                $qty = (int) $row->quantity;
                $before = (int) $row->quantity_before;
                $after = (int) $row->quantity_after;
                $in = 0;
                $out = 0;

                if (in_array($type, $nonPhysical, true)) {
                    $in = 0;
                    $out = 0;
                } elseif (in_array($type, $inbound, true) || ($type === 'adjustment' && $after > $before)) {
                    $in = $qty;
                } else {
                    $out = $qty;
                }

                DB::table('transactions')->where('id', $row->id)->update([
                    'quantity_in' => $in,
                    'quantity_out' => $out,
                    'balance_after' => $after,
                    'transaction_date' => $row->created_at,
                ]);
            }
        });
    }
};
