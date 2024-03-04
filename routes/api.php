<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Youaoi\MeCab\MeCab;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageTranslationController;
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
  Route::post('/signOut', [AuthController::class, 'signOut']);
  Route::resource('/note', WordOfWorldController::class);
  Route::post('/ocr', [ImageTranslationController::class, 'translateImage']);
});


Route::middleware('guest')->group(function () {
  Route::post('/auth-reset-password', [AuthController::class, 'resetPassword']);
});

Route::group(
  ['middleware' => ['web']],
  function () {
    Route::get('/social/{provider}', [SocialController::class, 'login'])->name('social.login');
    Route::get('/social/callback/{provider}', [SocialController::class, 'callback'])->name('social.callback');
  }
);

Route::post('vocabularyNote/User', [VocabularyNoteController::class, 'export'])->name('vocabularyNote.user');
Route::post('vocabularyNote/export', [VocabularyNoteController::class, 'export'])->name('vocabularyNote.export');
