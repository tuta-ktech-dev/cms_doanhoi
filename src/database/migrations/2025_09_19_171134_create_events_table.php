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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('union_id')->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('image', 255)->nullable();
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->string('location', 255)->nullable();
            $table->integer('max_participants')->nullable();
            $table->decimal('activity_points', 5, 2)->default(0);
            $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');
            $table->boolean('is_registration_open')->default(true);
            $table->datetime('registration_deadline')->nullable();
            $table->timestamps();
            
            $table->index('union_id');
            $table->index('status');
            $table->index('start_date');
            $table->index('is_registration_open');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
