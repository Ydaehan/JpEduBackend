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
    Schema::create('grammars', function (Blueprint $table) {
      $table->id();
      $table->string("grammar");
      $table->text("explain");
      $table->string("meaning");
      $table->string("conjunction");
      $table->foreignId('level_id')->constrained('levels')->onDelete('cascade')->onUpdate('cascade');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('grammars');
  }
};
