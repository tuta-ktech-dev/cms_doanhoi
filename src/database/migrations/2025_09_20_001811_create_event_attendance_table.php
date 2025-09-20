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
        Schema::create('event_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('registered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('attended_at');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Đảm bảo mỗi user chỉ có 1 record điểm danh cho 1 event
            $table->unique(['event_id', 'user_id']);
            
            // Indexes
            $table->index(['event_id', 'attended_at']);
            $table->index(['user_id', 'attended_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_attendance');
    }
};