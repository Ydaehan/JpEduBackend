<?php

namespace App\Http\Controllers;

use App\Services\Speech\AzureSpeechServicesApiClient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SpeechController extends Controller
{
  public function apiPostPronunciationAssessment(
    Request $request,
    AzureSpeechServicesApiClient $speechClient
  ) {
    $validator = Validator::make($request->all(), [
      'audio' => 'required|file',
      'text' => 'required|string'
    ]);

    $audio = $request->file('audio');
    $text = $request->get('text');

    return response()->json([
      "message" => "Success",
      "test" => $speechClient->assessPronunciation($text, $audio->getContent())
    ]);
  }
}
