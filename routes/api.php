<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
    Route::get('/social/{provider}', ['as' => 'social.login', 'uses' => 'App\Http\Controllers\SocialController@login']);
    Route::get('/social/callback/{provider}', ['as' => 'social.callback', 'uses' => 'App\Http\Controllers\SocialController@callback']);
  }
);
