<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory') && Schema::hasColumn('inventory', 'barcode')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->dropColumn('barcode');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory') && ! Schema::hasColumn('inventory', 'barcode')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->string('barcode')->nullable();
            });
        }
    }
};
