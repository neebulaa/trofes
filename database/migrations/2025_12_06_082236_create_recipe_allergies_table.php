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
        Schema::create('recipe_allergies', function (Blueprint $table) {
            $table->id('recipe_allergy_id');
            $table->foreignId('recipe_id')->constrained('recipes', 'recipe_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('allergy_id')->constrained('allergies', 'allergy_id')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_allergies');
    }
};
