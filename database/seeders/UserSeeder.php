<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    User::create([
      'nickname' => '이효빈',
      'email' => 'h@gmail.com',
      'password' => bcrypt('qwer1234'),
      'phone' => '01012345678',
      'birthday' => random_int(2000, 2020) . '-' . random_int(1, 12) . '-' . random_int(1, 28),
      'role' => 'user'
    ])->userSetting()->create();

    User::create([
      'nickname' => '심성환',
      'email' => 'sunghwan1332@naver.com',
      'password' => bcrypt('asdf1234'),
      'phone' => '01067831332',
      'birthday' => '2000-07-24',
      'role' => 'user'
    ])->userSetting()->create();

    User::create([
      'nickname' => '우성준',
      'email' => 'qwer1234@gmail.com',
      'password' => bcrypt('qwer1234'),
      'phone' => '01012345678',
      'birthday' => random_int(2000, 2020) . '-' . random_int(1, 12) . '-' . random_int(1, 28),
      'role' => 'user'
    ])->userSetting()->create();

    User::create([
      'nickname' => 'tokenUser',
      'email' => 'token@gmail.com',
      'password' => bcrypt('asdf1234'),
      'phone' => 'tokenPhone',
      'birthday' => random_int(2000, 2020) . '-' . random_int(1, 12) . '-' . random_int(1, 28),
      'role' => 'user'
    ])->userSetting()->create();
  }
}
