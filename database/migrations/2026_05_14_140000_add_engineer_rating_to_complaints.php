<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (! Schema::hasColumn('complaints', 'engineer_rating')) {
                $table->integer('engineer_rating')->nullable();
            }
            if (! Schema::hasColumn('complaints', 'engineer_rating_comment')) {
                $table->text('engineer_rating_comment')->nullable();
            }
            if (! Schema::hasColumn('complaints', 'rated_by')) {
                $table->unsignedBigInteger('rated_by')->nullable();
                $table->foreign('rated_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('complaints', 'rated_at')) {
                $table->timestamp('rated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'rated_by')) {
                $table->dropForeign(['rated_by']);
            }
            $cols = array_filter(
                ['engineer_rating', 'engineer_rating_comment', 'rated_by', 'rated_at'],
                fn ($col) => Schema::hasColumn('complaints', $col)
            );
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
