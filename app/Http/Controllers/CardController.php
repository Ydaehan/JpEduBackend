<?php

namespace App\Http\Controllers;

use App\Models\VocabularyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CardController extends Controller
{
  //

  public function index()
  {
    $user = Auth::user();
    $note = $user->vocabularyNotes()->get();
  }
}
