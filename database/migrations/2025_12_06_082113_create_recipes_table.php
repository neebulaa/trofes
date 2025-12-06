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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id('recipe_id');
            $table->string("title");
            $table->string("slug")->unique();
            $table->text("instructions");
            $table->text("measured_ingredients");
            $table->float('calories')->default(0);
            $table->float("protein")->default(0);
            $table->float("fat")->default(0);
            $table->float('sodium')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
