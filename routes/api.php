<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\VocabularyNoteController;

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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::post('/refresh', [AuthController::class, 'refreshToken']);
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
