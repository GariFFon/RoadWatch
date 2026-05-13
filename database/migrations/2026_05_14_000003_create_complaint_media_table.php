<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();

            // File classification
            $table->enum('file_type', ['image', 'video']);
            $table->enum('stage', ['before', 'during', 'after'])->default('before');

            // File info
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');

            // Cloud storage
            $table->string('cloud_disk')->default('s3'); // s3 | cloudinary | public
            $table->string('cloud_path');                // storage key/path
            $table->string('cloud_url');                 // full CDN URL
            $table->string('cloud_public_id')->nullable(); // Cloudinary only

            // Media dimensions
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->float('duration')->nullable();         // video duration in seconds
            $table->string('thumbnail_url')->nullable();   // video thumbnail

            // Moderation
            $table->boolean('is_flagged')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['complaint_id', 'stage']);
            $table->index(['complaint_id', 'file_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_media');
    }
};
