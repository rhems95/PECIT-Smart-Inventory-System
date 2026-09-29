<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('unit');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('quantity', 12, 4)->default(0);
            $table->decimal('reserved_quantity', 12, 4)->default(0);
            $table->decimal('minimum_stock', 12, 4)->default(10);
            $table->string('location')->nullable();
            $table->enum('status', ['available','low_stock','out_of_stock','discontinued'])->default('available');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('inventory'); }
};