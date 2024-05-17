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
    Schema::create('vocabulary_notes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
      $table->foreignId('level_id')->constrained('levels')->onDelete('cascade')->onUpdate('cascade');
      $table->string('title');
      $table->json('gana');
      $table->json('kanji');
      $table->json('meaning');
      $table->string('progress')->default('0%');
      $table->boolean('is_public')->default(false);
      $table->boolean('is_creator')->default(false);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('vocabulary_notes');
  }
};
