<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_size_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->string('size', 10);
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->timestamps();

            $table->unique(['inventory_id', 'size']);
        });

        // Move existing on-hand stock for sized uniforms into size "M" so totals stay consistent.
        $rows = DB::table('inventory')
            ->where('student_shop', true)
            ->get(['id', 'item_name', 'item_code', 'quantity', 'reserved_quantity']);

        $now = now();

        foreach ($rows as $row) {
            $haystack = strtolower(($row->item_name ?? '').' '.($row->item_code ?? ''));
            if (str_contains($haystack, 'lanyard')) {
                continue;
            }

            DB::table('inventory_size_stocks')->insert([
                'inventory_id' => $row->id,
                'size' => 'M',
                'quantity' => (int) $row->quantity,
                'reserved_quantity' => (int) $row->reserved_quantity,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_size_stocks');
    }
};
