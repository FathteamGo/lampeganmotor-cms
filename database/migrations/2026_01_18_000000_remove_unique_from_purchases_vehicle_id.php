<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop foreign key first if it exists (so we can modify indexes)
            try {
                $table->dropForeign(['vehicle_id']);
            } catch (\Throwable $e) {
                try {
                    $table->dropForeign('purchases_vehicle_id_foreign');
                } catch (\Throwable $e) {
                    // ignore if FK doesn't exist
                }
            }

            // Now drop unique index by convention first
            try {
                $table->dropUnique(['vehicle_id']);
            } catch (\Throwable $e) {
                // If that fails, try by index name used in dumps
                try {
                    $table->dropUnique('purchases_vehicle_id_unique');
                } catch (\Throwable $e) {
                    // As a last resort, run raw SQL to drop index if it exists
                    try {
                        DB::statement("ALTER TABLE purchases DROP INDEX IF EXISTS `purchases_vehicle_id_unique`");
                    } catch (\Throwable $e) {
                        // ignore if can't drop (index may not exist)
                    }
                }
            }

            // Add non-unique index for performance (optional)
            try {
                $table->index('vehicle_id');
            } catch (\Throwable $e) {
                // ignore if index already exists
            }

            // Recreate foreign key constraint (non-unique)
            try {
                $table->foreign('vehicle_id')->references('id')->on('vehicles');
            } catch (\Throwable $e) {
                // ignore if can't create (may already exist)
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop the non-unique index
            try {
                $table->dropIndex(['vehicle_id']);
            } catch (\Throwable $e) {
                try {
                    // try by name if needed
                    $table->dropIndex('purchases_vehicle_id_index');
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            // Recreate unique constraint
            try {
                $table->unique('vehicle_id');
            } catch (\Throwable $e) {
                // ignore if can't recreate
            }
        });
    }
};
