<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory') && Schema::hasColumn('inventory', 'supplier_id')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->dropConstrainedForeignId('supplier_id');
            });
        }

        Schema::dropIfExists('suppliers');
    }

    public function down(): void
    {
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_person')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('inventory') && ! Schema::hasColumn('inventory', 'supplier_id')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            });
        }
    }
};
