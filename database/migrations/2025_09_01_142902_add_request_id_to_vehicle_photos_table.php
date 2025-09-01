<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehicle_photos', function (Blueprint $table) {
            $table->foreignId('request_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('requests')
                  ->nullOnDelete();

            $table->unsignedTinyInteger('photo_order')->default(0)->after('caption');

            $table->index(['request_id', 'vehicle_id']);
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_photos', function (Blueprint $table) {
            $table->dropIndex(['request_id', 'vehicle_id']);
            $table->dropConstrainedForeignId('request_id');
            $table->dropColumn('photo_order');
        });
    }
};
