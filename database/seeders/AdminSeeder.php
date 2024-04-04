<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    User::create([
      'nickname' => 'Daehan',
      'email' => 'daehan821@naver.com',
      'password' => bcrypt('qwezxc123'),
      'phone' => '01068089833',
      'birthday' => '2000-05-19',
      'role' => 'admin'
    ]);

    User::create([
      'nickname' => 'Sunghwan',
      'email' => 'sunghwan1332@gmail.com',
      'password' => bcrypt('qwezxc123'),
      'phone' => '01067831332',
      'birthday' => '2000-07-24',
      'role' => 'admin'
    ]);
  }
}
