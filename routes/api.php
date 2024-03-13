<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageTranslationController;
use App\Http\Controllers\TypingPracticeController;
use App\Http\Controllers\VocabularyNoteController;
use App\Http\Controllers\WordOfWorldController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ManagerController;

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

// Route::middleware('guest')->group(function () {
//   Route::post('/auth-reset-password', [AuthController::class, 'resetPassword']);
// });

// Route::get('/verify', [AuthController::class, 'verifyUser'])->name('verify.user');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// 일반 유저
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::post('/refresh', [AuthController::class, 'refreshToken']);
  Route::delete('/sign-out', [AuthController::class, 'signOut']);
  Route::post('/wordOfWorld', [WordOfWorldController::class, 'result']);

  Route::prefix('/vocabularyNote')->group(function () {
    Route::post('/export', [VocabularyNoteController::class, 'export']);
    Route::post('/userCreate', [VocabularyNoteController::class, 'userCreate']);
    Route::patch('/update', [VocabularyNoteController::class, 'update']);
    Route::delete('/delete', [VocabularyNoteController::class, 'destroy']);
  });
  Route::post('/ocr', [ImageTranslationController::class, 'translateImage']);
  Route::get('/typing/getSentences', [TypingPracticeController::class, 'getSentences']);
});

// 매니저, 관리자
Route::middleware(['auth:sanctum', 'ability:manager,admin'])->group(function () {
  Route::prefix('/manager')->group(function () {
    Route::post('/register', [ManagerController::class, 'managerSignUp']);
    Route::delete('/deleteManagerWaitList/{email}', [ManagerController::class, 'deleteManagerWaitList']);
  });
});

// 타자 연습
Route::prefix('/typing')->group(function () {
  Route::post('/store', [TypingPracticeController::class, 'store']);
  Route::patch('/update/{id}', [TypingPracticeController::class, 'update']);
  Route::delete('/delete/{id}', [TypingPracticeController::class, 'destroy']);
});

// 메일
Route::prefix('mail')->group(function () {
  Route::post('/applyToManager', [MailController::class, 'applyToManager']);
  Route::post('/sendSignUpEmail/{email}', [MailController::class, 'sendSignUpEmail']);
});

// 단어장
Route::post('vocabularyNote/export', [VocabularyNoteController::class, 'export']);

// 소셜로그인
Route::prefix('/social')->group(function () {
  Route::get('/{provider}', [SocialController::class, 'login']);
  Route::get('/callback/{provider}', [SocialController::class, 'callback']);
  Route::get('/mobile/{provider}', [SocialController::class, 'mobileCallback']);
});
