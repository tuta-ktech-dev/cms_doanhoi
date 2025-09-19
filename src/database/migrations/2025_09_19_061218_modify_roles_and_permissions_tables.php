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
        // Sửa đổi bảng roles để không cho phép thay đổi name
        Schema::table('roles', function (Blueprint $table) {
            // Thêm cột is_system để đánh dấu role là hệ thống (không thể xóa)
            $table->boolean('is_system')->default(true)->after('description');
            
            // Thêm index cho cột is_system
            $table->index('is_system');
        });
        
        // Sửa đổi bảng permissions để không cho phép thay đổi name
        Schema::table('permissions', function (Blueprint $table) {
            // Thêm cột is_system để đánh dấu permission là hệ thống (không thể xóa)
            $table->boolean('is_system')->default(true)->after('description');
            
            // Thêm index cho cột is_system
            $table->index('is_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa cột is_system khỏi bảng roles
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['is_system']);
            $table->dropColumn('is_system');
        });
        
        // Xóa cột is_system khỏi bảng permissions
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['is_system']);
            $table->dropColumn('is_system');
        });
    }
};
