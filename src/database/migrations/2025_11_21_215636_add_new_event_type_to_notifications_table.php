<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify enum to add 'new_event' type
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('registration_success', 'unregistration_success', 'attendance_success', 'new_event') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum to original values
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('registration_success', 'unregistration_success', 'attendance_success') NOT NULL");
    }
};
