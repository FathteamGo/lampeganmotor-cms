<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyReportsTable extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('visitors')->default(0);
            $table->integer('sales_count')->default(0);
            $table->bigInteger('sales_total')->default(0);
            $table->bigInteger('income_total')->default(0);
            $table->bigInteger('expense_total')->default(0);
            $table->bigInteger('total_income')->default(0);
            $table->integer('stock')->default(0);
            $table->integer('stnk_renewal')->default(0);
            $table->json('top_motors')->nullable();
            $table->text('insight')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
}
