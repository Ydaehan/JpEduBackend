<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Grammar;

class GrammarSeeder extends Seeder
{
  private function getGrammarData()
  {
    $grammarFiles = ['JLPTN1.json', 'JLPTN2.json', 'JLPTN3.json'];
    foreach ($grammarFiles as $file) {
      $path = storage_path('app/grammar/' . $file);
      $json = json_decode(file_get_contents($path), true);

      foreach ($json['data'] as $item) {
        $grammar = new Grammar();
        $grammar->grammar = $item['Grammar'];
        $grammar->explain = $item['Description'];
        // 5번 반복하면 $index 를 1씩 증가시키며 $item['Example' . $index]를 가져와서 json 형식으로 저장
        $example = [];
        for ($index = 1; $index <= 5; $index++) {
          if (isset($item['Example' . $index]) && $item['Example' . $index] != "") {
            $example[$index] = $item['Example' . $index];
          } else {
            $example[$index] = null;
          }
        }
        $grammar->example = json_encode($example);
        $grammar->mean = $item['Meaning'];
        $grammar->conjunction = $item['Connection'];
        $grammar->tier = $json['tier'];
        $grammar->save();
      }
    }
  }
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->getGrammarData();
  }
}
