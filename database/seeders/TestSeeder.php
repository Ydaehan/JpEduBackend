<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TestSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $user = User::where('email', 'token@gmail.com')->first();
    $role = $user->role;

    // 테스트 토큰 생성
    $accessToken = $user->createToken('API Token', [$role], Carbon::now()->addMinutes(config('sanctum.test_expiration')));
    $token = $accessToken->plainTextToken;
    Log::info('Token: ' . $token);
  }
}
