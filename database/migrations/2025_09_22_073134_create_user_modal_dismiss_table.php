<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_modal_dismiss', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('modal_key'); // misal: 'weekly_report_reminder'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_modal_dismiss');
    }
};
