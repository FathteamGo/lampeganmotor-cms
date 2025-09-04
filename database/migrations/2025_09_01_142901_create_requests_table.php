<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')
                  ->constrained('suppliers')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('vehicle_model_id')->nullable()->constrained('vehicle_models')->nullOnDelete();
            $table->foreignId('year_id')->nullable()->constrained('years')->nullOnDelete();

            $table->unsignedInteger('odometer')->nullable();

            $table->enum('type', ['buy','sell'])->default('sell');
            $table->enum('status', ['available', 'sold', 'in_repair', 'hold', 'converted', 'rejected'])->default('hold');
            $table->text('notes')->nullable();

            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();

            $table->timestamps();

            $table->index(['supplier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
