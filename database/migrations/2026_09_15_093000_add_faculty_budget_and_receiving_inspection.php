<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('departments') && ! Schema::hasColumn('departments', 'faculty_budget_limit')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->decimal('faculty_budget_limit', 12, 2)->default(10000);
            });
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'inspection_status')) {
                $table->string('inspection_status', 20)->nullable();
            }
            if (! Schema::hasColumn('transactions', 'inspection_notes')) {
                $table->text('inspection_notes')->nullable();
            }
            if (! Schema::hasColumn('transactions', 'inspected_by')) {
                $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('transactions', 'inspected_at')) {
                $table->timestamp('inspected_at')->nullable();
            }
        });

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'inspection_status')) {
            DB::table('transactions')
                ->whereIn('type', ['stock_in', 'purchase_delivery'])
                ->whereNull('inspection_status')
                ->update(['inspection_status' => 'pending']);
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'inspected_by')) {
                $table->dropConstrainedForeignId('inspected_by');
            }
            foreach (['inspection_status', 'inspection_notes', 'inspected_at'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'faculty_budget_limit')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('faculty_budget_limit');
            });
        }
    }
};
