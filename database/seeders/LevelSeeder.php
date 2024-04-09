<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Level;

class LevelSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    for ($i = 1; $i < 6; $i++) {
      Level::create([
        'level' => 'N' . $i
      ]);
    }

    Level::create([
      'level' => 'Total'
    ]);

    Level::create([
      'level' => 'UserCustom'
    ]);

    Level::create([
      'level' => 'Review'
    ]);
  }
}
