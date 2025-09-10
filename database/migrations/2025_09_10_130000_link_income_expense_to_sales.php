<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('incomes', function (Blueprint $table) {
            if (!Schema::hasColumn('incomes', 'sale_id')) {
                $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'sale_id')) {
                $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            }
        });
    }

    public function down(): void {
        Schema::table('incomes', function (Blueprint $table) {
            if (Schema::hasColumn('incomes', 'sale_id')) $table->dropConstrainedForeignId('sale_id');
        });
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'sale_id')) $table->dropConstrainedForeignId('sale_id');
        });
    }
};