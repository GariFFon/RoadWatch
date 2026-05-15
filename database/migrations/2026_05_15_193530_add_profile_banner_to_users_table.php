<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // S3 URL of the user's profile banner image
            $table->string('profile_banner_url')->nullable()->after('profile_photo');

            // Migrate profile_photo to store S3 URLs directly (was relative paths)
            // profile_photo_url accessor already handles this
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_banner_url');
        });
    }
};
