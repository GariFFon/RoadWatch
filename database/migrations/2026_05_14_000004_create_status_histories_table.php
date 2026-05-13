<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();

            // Status transition
            $table->string('old_status');
            $table->string('new_status');
            $table->text('remarks')->nullable();

            // Time analytics — seconds spent in the old_status before this change
            $table->unsignedInteger('time_in_previous_status')->nullable();

            // Immutable — no updated_at
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('complaint_id');
            $table->index('new_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
