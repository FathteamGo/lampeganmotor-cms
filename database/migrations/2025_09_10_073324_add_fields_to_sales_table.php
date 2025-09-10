<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'remaining_payment')) {
                $table->decimal('remaining_payment', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('sales', 'due_date')) {
                $table->date('due_date')->nullable();
            }
            if (!Schema::hasColumn('sales', 'status')) {
                $table->enum('status', ['proses', 'kirim', 'selesai'])->default('proses');
            }
            if (!Schema::hasColumn('sales', 'cmo')) {
                $table->string('cmo')->nullable();
            }
            if (!Schema::hasColumn('sales', 'cmo_fee')) {
                $table->decimal('cmo_fee', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('sales', 'direct_commission')) {
                $table->decimal('direct_commission', 15, 2)->nullable()->after('cmo_fee');
            }
            if (!Schema::hasColumn('sales', 'order_source')) {
                $table->enum('order_source', ['fb', 'ig', 'tiktok', 'walk_in'])->nullable();
            }
            if (!Schema::hasColumn('sales', 'result')) {
                $table->enum('result', ['ACC', 'CASH', 'CANCEL'])->nullable();
            }
            if (!Schema::hasColumn('sales', 'branch_name')) {
                $table->string('branch_name')->nullable();
            }
            if (!Schema::hasColumn('sales', 'dp_po')) {
                $table->decimal('dp_po', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('sales', 'dp_real')) {
                $table->decimal('dp_real', 15, 2)->nullable();
            }
        });

        if (Schema::hasColumn('sales', 'result')) {
            DB::statement("ALTER TABLE `sales` MODIFY `result` ENUM('ACC','CASH','CANCEL') NULL");
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'dp_real')) $table->dropColumn('dp_real');
            if (Schema::hasColumn('sales', 'dp_po')) $table->dropColumn('dp_po');
            if (Schema::hasColumn('sales', 'branch_name')) $table->dropColumn('branch_name');
            if (Schema::hasColumn('sales', 'result')) $table->dropColumn('result');
            if (Schema::hasColumn('sales', 'order_source')) $table->dropColumn('order_source');
            if (Schema::hasColumn('sales', 'direct_commission')) $table->dropColumn('direct_commission');
            if (Schema::hasColumn('sales', 'cmo_fee')) $table->dropColumn('cmo_fee');
            if (Schema::hasColumn('sales', 'cmo')) $table->dropColumn('cmo');
            if (Schema::hasColumn('sales', 'status')) $table->dropColumn('status');
            if (Schema::hasColumn('sales', 'due_date')) $table->dropColumn('due_date');
            if (Schema::hasColumn('sales', 'remaining_payment')) $table->dropColumn('remaining_payment');
            if (Schema::hasColumn('sales', 'user_id')) $table->dropConstrainedForeignId('user_id');
        });
    }
};
