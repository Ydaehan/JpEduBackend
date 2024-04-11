<?php

namespace Database\Seeders;

use App\Models\Score;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScoreSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $category = ['JLPT', 'WorldOfWords', 'CardMatching'];
    for ($i = 0; $i < 100; $i++) {
      $selectedCategory = $category[array_rand($category)];
      Score::create([
        'user_id' => rand(11, 15),
        'level_id' => rand(1, 8),
        'score' => rand(0, 100),
        'category' => $selectedCategory
      ]);
    }
  }
}
