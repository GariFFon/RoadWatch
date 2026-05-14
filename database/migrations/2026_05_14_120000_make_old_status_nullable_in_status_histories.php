<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make old_status nullable so the initial "Complaint Filed → Pending"
     * history entry can be created without a previous status.
     */
    public function up(): void
    {
        Schema::table('status_histories', function (Blueprint $table) {
            $table->string('old_status')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('status_histories', function (Blueprint $table) {
            $table->string('old_status')->nullable(false)->change();
        });
    }
};
