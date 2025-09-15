<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE requests 
            MODIFY status ENUM('available','sold','in_repair','hold','converted','rejected') 
            NOT NULL DEFAULT 'hold'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE requests 
            MODIFY status ENUM('available','sold','in_repair','hold') 
            NOT NULL DEFAULT 'hold'
        ");
    }
};
