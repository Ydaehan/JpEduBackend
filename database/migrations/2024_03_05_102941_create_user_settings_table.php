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
    Schema::create('user_settings', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
      $table->boolean('review_note_auto_register')->default(true);
      $table->boolean('score_auto_register')->default(true);
      $table->boolean('vocabulary_note_auto_visibility')->default(false);
      $table->string('avatar')->default('default_avatar');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_settings');
  }
};
