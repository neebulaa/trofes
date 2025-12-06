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
        Schema::create('recipe_dietary_preferences', function (Blueprint $table) {
            $table->id('recipe_dietary_preference_id');
            $table->foreignId('recipe_id')->constrained('recipes', 'recipe_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('dietary_preference_id')->constrained('dietary_preferences', 'dietary_preference_id')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_dietary_preferences');
    }
};
