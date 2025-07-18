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
        Schema::table('survey_responses', function (Blueprint $table) {
            // Add new polymorphic columns
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type')->nullable();

            // Make line_id nullable for backward compatibility
            $table->unsignedBigInteger('line_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'user_type']);
            $table->unsignedBigInteger('line_id')->nullable(false)->change();
        });
    }
};
