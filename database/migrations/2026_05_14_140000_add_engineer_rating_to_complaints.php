<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->integer('engineer_rating')->nullable();
            $table->text('engineer_rating_comment')->nullable();
            $table->unsignedBigInteger('rated_by')->nullable();
            $table->timestamp('rated_at')->nullable();

            $table->foreign('rated_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['rated_by']);
            $table->dropColumn(['engineer_rating', 'engineer_rating_comment', 'rated_by', 'rated_at']);
        });
    }
};
