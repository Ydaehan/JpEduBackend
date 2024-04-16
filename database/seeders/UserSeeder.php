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
      'nickname' => 'testUser1',
      'email' => 'asdf1234@naver.com',
      'password' => bcrypt('asdf1234'),
      'phone' => '01012345678',
      'birthday' => random_int(2000, 2020) . '-' . random_int(1, 12) . '-' . random_int(1, 28),
      'role' => 'user'
    ])->userSetting()->create([
      'user_id' => '11',
    ]);;

    User::create([
      'nickname' => 'testUser2',
      'email' => 'asdf1235@gmail.com',
      'password' => bcrypt('asdf1235'),
      'phone' => '01012345678',
      'birthday' => random_int(2000, 2020) . '-' . random_int(1, 12) . '-' . random_int(1, 28),
      'role' => 'user'
    ])->userSetting()->create();

    User::create([
      'nickname' => 'testUser3',
      'email' => 'asdf1236@gmail.com',
      'password' => bcrypt('asdf1236'),
      'phone' => '01012345678',
      'birthday' => random_int(2000, 2020) . '-' . random_int(1, 12) . '-' . random_int(1, 28),
      'role' => 'user'
    ])->userSetting()->create([
      'user_id' => '13',
    ]);

    User::create([
      'nickname' => 'testUser4',
      'email' => 'asdf1237@gmail.com',
      'password' => bcrypt('asdf1237'),
      'phone' => '01012345678',
      'birthday' => random_int(2000, 2020) . '-' . random_int(1, 12) . '-' . random_int(1, 28),
      'role' => 'user'
    ])->userSetting()->create([
      'user_id' => '14',
    ]);;

    User::create([
      'nickname' => 'testUser5',
      'email' => 'asdf1238@gmail.com',
      'password' => bcrypt('asdf1238'),
      'phone' => '01012345678',
      'birthday' => random_int(2000, 2020) . '-' . random_int(1, 12) . '-' . random_int(1, 28),
      'role' => 'user'
    ])->userSetting()->create([
      'user_id' => '15',
    ]);;
  }
}
