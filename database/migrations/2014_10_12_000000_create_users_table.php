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
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('nickname')->nullable();
      $table->string('email')->nullable();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password')->nullable();
      $table->string('phone')->nullable();
      $table->date('birthday')->nullable();
      $table->string('avatar')->nullable();
    //   $table->string('verification_code')->nullable(); // 이메일 인증코드
    //   $table->integer('is_verified')->default(0); // 인증 여부
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
