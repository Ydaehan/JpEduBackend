<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageTranslationController;
use App\Http\Controllers\TypingPracticeController;
use App\Http\Controllers\VocabularyNoteController;
use App\Http\Controllers\WordOfWorldController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::get('/verify', [AuthController::class, 'verifyUser'])->name('verify.user');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::post('/refresh', [AuthController::class, 'refreshToken']);
  Route::delete('/sign-out', [AuthController::class, 'signOut']);
  Route::post('/wordOfWorld', [WordOfWorldController::class, 'result']);
  Route::post('vocabularyNote/userCreate', [VocabularyNoteController::class, 'userCreate']);
  Route::post('/ocr', [ImageTranslationController::class, 'translateImage']);
  Route::get('/typing/getSentences', [TypingPracticeController::class, 'getSentences']);
});

Route::post('/typing/makeSentences', [TypingPracticeController::class, 'makeSentences']);

Route::post('vocabularyNote/export', [VocabularyNoteController::class, 'export']);

Route::middleware('guest')->group(function () {
  Route::post('/auth-reset-password', [AuthController::class, 'resetPassword']);
});

Route::get('/social/{provider}', [SocialController::class, 'login']);
Route::get('/social/callback/{provider}', [SocialController::class, 'callback']);
Route::get('/social/mobile/{provider}', [SocialController::class, 'mobileCallback']);
