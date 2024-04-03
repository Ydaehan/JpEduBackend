<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TypingPracticeController;
use App\Http\Controllers\VocabularyNoteController;
use App\Http\Controllers\WordOfWorldController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\GrammarController;
use App\Http\Controllers\PronunciationController;
use Illuminate\Support\Facades\Http;

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
Route::middleware(['auth:sanctum', 'ability:refresh'])->group(function () {
	Route::post('/refresh', [AuthController::class, 'refreshToken']);
});

Route::middleware(['auth:sanctum', 'ability:user'])->group(function () {
	Route::get('/user', function (Request $request) {
		return $request->user();
	});
	Route::post('/logout', [AuthController::class, 'logout']);
	Route::delete('/sign-out', [AuthController::class, 'signOut']);
	Route::patch('/user', [AuthController::class, 'update']);
	Route::post('/wordOfWorld', [WordOfWorldController::class, 'result']);
	// 단어장
	Route::resource('/vocabularyNote', VocabularyNoteController::class)->except(['create', 'edit']);
	Route::prefix('/vocabularyNote')->group(function () {
		Route::post('/export', [VocabularyNoteController::class, 'export']);
		Route::post('/ocr', [VocabularyNoteController::class, 'textOcr']);
	});
	Route::get('/typing/getSentences', [TypingPracticeController::class, 'getSentences']);
	Route::prefix('/jlpt')->group(function () {
		Route::resource('/grammar', GrammarController::class)->except(['index', 'create', 'edit']);
	});
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

// test token 생성
Route::post('/test-token', [AuthController::class, 'createTestToken']);
