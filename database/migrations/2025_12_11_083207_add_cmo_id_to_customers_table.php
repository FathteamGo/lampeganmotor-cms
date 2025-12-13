<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('customers', function (Blueprint $table) {
        $table->foreignId('cmo_id')->nullable()->constrained('users')->nullOnDelete();
    });
}

public function down()
{
    Schema::table('customers', function (Blueprint $table) {
        $table->dropForeign(['cmo_id']);
        $table->dropColumn('cmo_id');
    });
}
};
