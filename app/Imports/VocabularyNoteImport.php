<?php

namespace App\Imports;


use Maatwebsite\Excel\Concerns\ToCollection;
use \Illuminate\Support\Collection;
use Exception;

class VocabularyNoteImport implements ToCollection
{
  /**
   * @param array $row
   *
   * @return \Illuminate\Database\Eloquent\Model|null
   */

  private $vocabularyNote = array();



  // public function collection(Collection $collection)
  // {
  //   try {
  //     list($kanji, $gana, $meaning) = array_map(null, ...$collection->slice(1, -1)->values());

  //     for ($i = 1; $i < $collection->count() - 1; $i++) {
  //       $meaning[] =  $collection[$i][2];
  //       $gana[] = $collection[$i][5];
  //       $kanji[] = $collection[$i][6];
  //     }
  //     // for ($i = 1; $i < $collection->count() - 1; $i++) {
  //     //   $kanji[] = $collection[$i][0];
  //     //   $gana[] = $collection[$i][1];
  //     //   $meaning[] =  $collection[$i][2];
  //     // }

  //     $result = duplicateCheck($kanji, $gana, $meaning);
  //     list($kanji, $gana, $meaning) = $result;

  //     $this->vocabularyNote = [
  //       'gana' => $gana,
  //       'kanji' => $kanji,
  //       'meaning' => $meaning
  //     ];
  //   } catch (Exception $e) {
  //     return response()->json(['status' => 'Fail', 'message' => 'VocabularyNoteImport: ' . $e->getMessage()], 400);
  //   }
  //   return;
  // }
  public function collection(Collection $collection)
  {
    try {
      list($kanji, $gana, $meaning) = array_map(null, ...$collection->slice(1, -1)->values()->toArray());

      list($kanji, $gana, $meaning) = duplicateCheck($kanji, $gana, $meaning);

      $this->vocabularyNote = [
        'gana' => $gana,
        'kanji' => $kanji,
        'meaning' => $meaning
      ];
    } catch (Exception $e) {
      throw new Exception('VocabularyNoteImport: ' . $e->getMessage(), 400);
    }
  }

  public function getVocabularyNote()
  {
    return $this->vocabularyNote;
  }
}
