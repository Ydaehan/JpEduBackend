<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Grammar;
use App\Models\GrammarExample;
use Illuminate\Support\Str;

class GrammarSeeder extends Seeder
{
  private function getGrammarData()
  {
    $grammarFiles = getFilesFromS3('grammars');
    foreach ($grammarFiles as $file) {
      $path = env('AWS_S3_URL') . $file;
      $json = json_decode(file_get_contents($path), true);
      foreach ($json['data'] as $item) {
        $grammar = Grammar::create([
          'grammar' => $item['Grammar'],
          'explain' => $item['Description'],
          'meaning' => $item['Meaning'],
          'conjunction' => $item['Connection'],
          'level_id' => Str::after($json['tier'], 'N')
        ]);
        // 5번 반복하면 $index 를 1씩 증가시키며 $item['Example' . $index]를 가져와서 json 형식으로 저장
        for ($index = 1; $index <= 5; $index++) {
          if (isset($item['Example' . $index]) && $item['Example' . $index] != "") {
            $grammar->grammarExamples()->create([
              'grammar_id' => $grammar->id,
              'user_id' => 1,
              'example' => $item['Example' . $index]
            ]);
          }
        }
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
