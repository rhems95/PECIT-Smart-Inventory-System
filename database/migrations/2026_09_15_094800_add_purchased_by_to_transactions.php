<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transactions', 'purchased_by')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('purchased_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('transactions', 'purchased_by')) {
            DB::table('transactions')
                ->whereIn('type', ['stock_in', 'purchase_delivery'])
                ->whereNull('purchased_by')
                ->update(['purchased_by' => DB::raw('performed_by')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'purchased_by')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('purchased_by');
            });
        }
    }
};
