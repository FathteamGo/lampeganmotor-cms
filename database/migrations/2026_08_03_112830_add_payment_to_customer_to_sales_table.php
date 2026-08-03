<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'payment_to_customer')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('payment_to_customer', 15, 2)->nullable()->after('dp_real');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sales', 'payment_to_customer')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('payment_to_customer');
            });
        }
    }
};
