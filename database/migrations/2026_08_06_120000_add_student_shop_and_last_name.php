<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->boolean('student_shop')->default(false)->after('status');
            $table->foreignId('department_id')->nullable()->after('student_shop')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn('student_shop');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_name');
        });
    }
};
