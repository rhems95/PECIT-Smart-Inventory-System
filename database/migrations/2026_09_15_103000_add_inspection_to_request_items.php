<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            if (! Schema::hasColumn('request_items', 'inspection_status')) {
                $table->string('inspection_status', 20)->nullable();
            }
            if (! Schema::hasColumn('request_items', 'inspection_notes')) {
                $table->text('inspection_notes')->nullable();
            }
            if (! Schema::hasColumn('request_items', 'inspected_by')) {
                $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('request_items', 'inspected_at')) {
                $table->timestamp('inspected_at')->nullable();
            }
        });

        if (Schema::hasColumn('request_items', 'inspection_status')) {
            DB::table('request_items')
                ->where('quantity_released', '>', 0)
                ->whereNull('inspection_status')
                ->update(['inspection_status' => 'pending']);
        }
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            if (Schema::hasColumn('request_items', 'inspected_by')) {
                $table->dropConstrainedForeignId('inspected_by');
            }
            foreach (['inspection_status', 'inspection_notes', 'inspected_at'] as $column) {
                if (Schema::hasColumn('request_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
