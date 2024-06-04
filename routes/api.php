<?php


use App\Http\Controllers\SpeechController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TypingPracticeController;
use App\Http\Controllers\VocabularyNoteController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\GrammarController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\S3Controller;
use App\Http\Controllers\SentenceNoteController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

// 비 토큰 인증 라우트
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// 토큰 갱신
Route::middleware(['auth:sanctum', 'ability:refresh'])->group(function () {
  Route::post('/refresh', [AuthController::class, 'refreshToken']);
});

// 일반 유저
Route::middleware(['auth:sanctum', 'ability:user'])->group(function () {
  Route::get('/user', function (Request $request) {
    return $request->user();
  });
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::delete('/sign-out', [AuthController::class, 'signOut']);
  Route::patch('/user', [AuthController::class, 'update']);
  Route::post('/gameResult', [GameController::class, 'gameResult']);
  Route::get('/gameNote', [GameController::class, 'index']);
  Route::prefix('/speech')->group(function () {
    Route::post('', [SpeechController::class, 'pronunciationAssessment']);
    Route::post('/translate', [SpeechController::class, 'translate']);
    Route::post('/tts', [SpeechController::class, 'tts']);
  });
  // 단어장
  Route::resource('/vocabularyNote', VocabularyNoteController::class)->except(['create', 'edit']);
  Route::prefix('/vocabularyNote')->group(function () {
    Route::post('/export', [VocabularyNoteController::class, 'export']);
    Route::post('/ocr', [VocabularyNoteController::class, 'textOcr']);
    Route::get('/public/notes', [VocabularyNoteController::class, 'publicIndex']);
    Route::post('/copy/{noteId}', [VocabularyNoteController::class, 'noteCopy']);
    Route::post('/progress/{noteId}', [VocabularyNoteController::class, 'progressUpdate']);
  });
  // 문장 노트
  Route::prefix('/sentenceNotes')->group(function () {
    Route::get('/lists', [SentenceNoteController::class, 'sentenceNoteLists']);
    Route::get('/get/{id}', [SentenceNoteController::class, 'getSentenceNote']);
    Route::post('/make', [SentenceNoteController::class, 'create']);
    Route::patch('/update', [SentenceNoteController::class, 'update']);
    Route::delete('/delete/{id}', [SentenceNoteController::class, 'destroy']);
  });
  Route::prefix('/typing')->group(function () {
    Route::get('/getSentences', [TypingPracticeController::class, 'getSentences']);
  });
  Route::prefix('/jlpt')->group(function () {
    Route::get('/grammar', [GrammarController::class, 'index']);
    Route::post('/grammar', [GrammarController::class, 'create']);
    Route::delete('/grammar/{id}', [GrammarController::class, 'delete']);
  });
  // 랭킹
  Route::prefix('/ranking')->group(function () {
    Route::get('/myScore', [RankingController::class, 'getAllMyScore']);
    Route::get('/{category}', [RankingController::class, 'getCategoryRanking']);
  });
});

// 매니저, 관리자
Route::middleware(['auth:sanctum', 'ability:manager,admin'])->group(function () {
  Route::prefix('/manager')->group(function () {
    Route::post('/register', [ManagerController::class, 'managerSignUp']);
  });
  Route::prefix('admin')->group(function () {
    Route::delete('/jlpt/grammar/{id}', [GrammarController::class, 'delete']);
    Route::delete('/deleteManagerWaitList', [ManagerController::class, 'deleteManagerWaitList']);
  });
  // 타자 연습
  Route::prefix('/typing')->group(function () {
    Route::post('/store', [TypingPracticeController::class, 'store']);
    Route::patch('/update/{id}', [TypingPracticeController::class, 'update']);
    Route::delete('/delete/{id}', [TypingPracticeController::class, 'destroy']);
  });
});


// 메일
Route::prefix('mail')->group(function () {
  Route::post('/applyToManager', [MailController::class, 'applyToManager']);
  Route::post('/sendSignUpEmail', [MailController::class, 'sendSignUpEmail']);
});

// 소셜로그인
Route::prefix('/social')->group(function () {
  Route::get('/{provider}', [SocialController::class, 'login']);
  Route::get('/callback/{provider}', [SocialController::class, 'callback']);
  Route::get('/mobile/{provider}', [SocialController::class, 'mobileCallback']);
});

// 관리자가 문법 생성
Route::prefix('/jlpt')->group(function () {
  Route::post('/grammar', [GrammarController::class, 'create']);
});

// s3 test
Route::post('/s3', [S3Controller::class, 'store']);
Route::get('/s3-files', [S3Controller::class, 'getS3Files']);

// sentenceNoteOcrTest
Route::post('/sentenceNotes/image', [SentenceNoteController::class, 'imageOcr']);
