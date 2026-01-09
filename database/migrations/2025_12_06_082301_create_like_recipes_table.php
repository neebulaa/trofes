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
        Schema::create('like_recipes', function (Blueprint $table) {
            $table->id('like_recipe_id');
            $table->foreignId('recipe_id')->constrained('recipes', 'recipe_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('liked_at')->default(now());
            $table->timestamps();

            $table->unique(['recipe_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('like_recipes');
    }
};
