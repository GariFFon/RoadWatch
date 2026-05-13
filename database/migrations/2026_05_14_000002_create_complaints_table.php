<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            // Unique human-readable reference e.g. RW-2025-00042
            $table->string('complaint_number')->unique();

            // Ownership & Assignment
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Complaint Details
            $table->string('title');
            $table->text('description');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location');           // human-readable address

            // Classification
            $table->enum('severity', ['low', 'medium', 'high', 'emergency'])->default('medium');
            $table->enum('status', ['pending', 'under_review', 'in_progress', 'resolved', 'rejected'])->default('pending');

            // Metrics
            $table->unsignedInteger('votes_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);

            // Flags
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_duplicate')->default(false);
            $table->foreignId('duplicate_of')->nullable()->constrained('complaints')->nullOnDelete();

            // Resolution
            $table->text('rejection_reason')->nullable();
            $table->date('estimated_completion')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index('status');
            $table->index('severity');
            $table->index('category_id');
            $table->index('assigned_to');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
