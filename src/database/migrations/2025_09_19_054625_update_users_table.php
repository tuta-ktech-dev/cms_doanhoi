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
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name', 100)->after('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->enum('role', ['admin', 'union_manager', 'student'])->default('student');
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->timestamp('last_login')->nullable();
            
            $table->index('role');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'phone',
                'avatar',
                'role',
                'status',
                'last_login'
            ]);
        });
    }
};
