<?php

namespace App\Http\Controllers;

use App\Models\VocabularyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WordOfWorldController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    //
    $user = Auth::user();
    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    // 관리자 생성 문제 찾아서 같이 넘겨주기

    $notes = VocabularyNote::where('user_id', $user->id)->get();

    return response()->json(["status" => "Success", "data" => $notes], 200);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    // 
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $request->validate([
      'score' => 'required|numeric|min:0',
    ]);
    // 
    $user = Auth::user();
    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }





    $vocabularyNote = new VocabularyNote;
    $vocabularyNote->user_id = $user->id;

    $vocabularyNote->save();
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
