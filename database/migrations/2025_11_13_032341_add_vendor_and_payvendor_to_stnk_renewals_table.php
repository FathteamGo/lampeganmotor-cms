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
        Schema::table('stnk_renewals', function (Blueprint $table) {
            $table->string('vendor')->nullable();
            $table->decimal('payvendor', 15, 2)->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stnk_renewals', function (Blueprint $table) {
            //
        });
    }
};
