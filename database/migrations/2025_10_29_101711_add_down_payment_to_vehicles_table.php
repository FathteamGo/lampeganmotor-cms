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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('down_payment', 15, 2)->nullable()->after('sale_price');
        });
    }

   public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('down_payment');
        });
    }

};
