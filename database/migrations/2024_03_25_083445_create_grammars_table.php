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
      $table->string("grammar")->unique();
      $table->text("explain");
      $table->jsonb("example"); // key value 값으로 key 에 유저아이디, value 에 유저가 추가한 문장 저장
      $table->string("mean");
      $table->string("conjunction");
      $table->string("tier");
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
