<?php

namespace App\Imports;

use App\Models\VocabularyNote;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use \Illuminate\Support\Collection;
use Exception;

class VocabularyNoteImport implements ToCollection
{
  /**
   * @param array $row
   *
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  private $title;
  private $is_public;

  public function __construct(string $title, bool $is_public = false)
  {
    $this->title = $title;
    $this->is_public = $is_public;
  }

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

      return [
        'status' => 'Success',
        'title' => $this->title,
        'user_id' => 1,
        'is_public' => $this->is_public,
        'gana' => $gana,
        'kanji' => $kanji,
        'meaning' => $meaning
      ];

      // return response()->json([
      //   'status' => 'Success',
      //   'title' => $this->title,
      //   // 'user_id' => auth()->user()->id,
      //   'user_id' => 1,
      //   'is_public' => $this->is_public,
      //   'gana' => $gana,
      //   'kanji' => $kanji,
      //   'meaning' => $meaning
      // ]);


    } catch (Exception $e) {
      return response()->json(['status' => 'Fail', 'message' => 'VocabularyNoteImport: ' . $e->getMessage()]);
    }
    return;
  }
}
