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
    Schema::create('sentence_notes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
      $table->string('title');
      $table->json('sentences');
      $table->string('situation');
      $table->timestamps();
    });
  }

<<<<<<< HEAD
  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('sentences_notes');
  }
=======
	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('sentence_notes');
	}
>>>>>>> 3e1c17e2024c7bbe90b692796fc690f6fea1e299
};
