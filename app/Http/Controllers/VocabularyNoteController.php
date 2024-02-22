<?php

namespace App\Http\Controllers;

use App\Imports\VocabularyNoteImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class VocabularyNoteController extends Controller
{
  //
  public function export(Request $request)
  {
    try {
      if (!$request->file('excel')) {
        return response()->json(["status" => "Fail", "message" => "No file"], 400);
      }
      // $is_)public = $request->get('is_public');
      $response = Excel::import(new VocabularyNoteImport("Test"), $request->file('excel'));
      return response()->json($response, 200);
    } catch (Exception $e) {
      return response()->json(["status" => "Fail", "message" => "VocabularyNoteController: " . $e->getMessage()], 400);
    }
  }
}
