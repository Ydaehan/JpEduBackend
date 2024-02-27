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



  public function collection(Collection $collection)
  {


    try {
      $meaning = array();
      $gana = array();
      $kanji = array();

      for ($i = 1; $i < $collection->count() - 1; $i++) {
        $meaning[] =  $collection[$i][2];
        $gana[] = $collection[$i][5];
        $kanji[] = $collection[$i][6];
      }
      // for ($i = 1; $i < $collection->count() - 1; $i++) {
      //   $kanji[] = $collection[$i][0];
      //   $gana[] = $collection[$i][1];
      //   $meaning[] =  $collection[$i][2];
      // }


      $this->vocabularyNote = [
        'status' => 'Success',
        // 'user_id' => auth()->user()->id,
        'user_id' => 1,
        'gana' => $gana,
        'kanji' => $kanji,
        'meaning' => $meaning
      ];
    } catch (Exception $e) {
      return response()->json(['status' => 'Fail', 'message' => 'VocabularyNoteImport: ' . $e->getMessage()], 400);
    }
    return;
  }

  public function getVocabularyNote()
  {
    return $this->vocabularyNote;
  }
}
