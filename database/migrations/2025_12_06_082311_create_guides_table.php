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
        Schema::create('guides', function (Blueprint $table) {
            $table->id('guide_id');
            $table->string('slug')->unique();
            $table->dateTime('published_at')->default(now());
            $table->text('content');
            $table->string('title');
            $table->string('image');
            $table->foreignId('admin_id')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};
