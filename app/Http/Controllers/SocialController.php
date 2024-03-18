<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Models\SocialAccount;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Laravel\Socialite\Contracts\Provider;

class SocialController extends Controller
{

  /**
   * @OA\Get (
   *     path="/api/social/{provider}",
   *     tags={"SocialAuth"},
   *     summary="소셜 로그인 URL 생성",
   *     description="소셜 로그인 URL을 생성합니다.",
   *     @OA\Parameter(
   *         name="provider",
   *         in="path",
   *         required=true, 
   *         description="kakao, google, naver, github 중 하나의 provider",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function login(string $provider)
  {
    if (!array_key_exists($provider, config('services'))) {
      return redirect('login')->with('error', $provider . ' 지원하지 않는 서비스입니다.');
    }

    return response()->json([
      'url' => Socialite::driver($provider)
        ->stateless()
        ->redirect()
        ->getTargetUrl(),
    ]);
  }



  /**
   * @OA\Get (
   *     path="/api/social/callback/{provider}{location.search}",
   *     tags={"SocialAuth"},
   *     summary="소셜 로그인 콜백 처리",
   *     description="소셜 회원 로그인",
   *     @OA\Parameter(
   *         name="provider",
   *         in="path",
   *         required=true,
   *         description="kakao, google, naver, github 중 하나의 provider",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Parameter(
   *         name="location.search",
   *         in="path",
   *         required=true,
   *         description="url",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function callback(string $provider)
  {
    try {
      $socialUser = Socialite::driver($provider)->stateless()->user();
      $socialAccount = SocialAccount::where('provider_name', $provider)
        ->where('provider_id', $socialUser->getId())
        ->first();

      if ($socialAccount) {
        $user = $socialAccount->user;

        $user->tokens()->delete();
        $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

        return response()->json([
          'status' => 'Success',
          'user' => $user,
          'access_token' => $accessToken->plainTextToken,
          'refresh_token' => $refreshToken->plainTextToken,
        ], 200);
      }

      $user = User::where('email', $socialUser->getEmail())->first();

      if (!$user) {
        $user = User::create([
          'email' => $socialUser->getEmail(),
          'nickname' => $socialUser->getNickname() ?? $socialUser->getName(),
        ]);
        $user->userSetting()->create();
      }

      $user->socialAccounts()->create([
        'provider_name' => $provider,
        'provider_id' => $socialUser->getId(),
      ]);

      $user->tokens()->delete();

      $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
      $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

      return response()->json([
        'status' => 'Success',
        'user' => $user,
        'access_token' => $accessToken->plainTextToken,
        'refresh_token' => $refreshToken->plainTextToken,
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        'status' => 'Fail',
        'message' => 'Social Login Fail',
      ], 400);
    }
  }



  /**
   * @OA\Get (
   *     path="/api/social/mobile/{provider}",
   *     tags={"SocialAuth"},
   *     summary="소셜 로그인 콜백 처리",
   *     description="소셜 회원 로그인",
   *     @OA\Parameter(
   *         name="provider",
   *         in="path",
   *         required=true,
   *         description="kakao, google, naver, github 중 하나의 provider",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer provider token",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function mobileCallback(string $provider, Request $request)
  {
    try {
      $token = $request->bearerToken();
      if (!$token) {
        return response()->json(['error' => 'Invalid credentials provided.'], 422);
      }

      $socialUser = Socialite::driver($provider)->userFromToken($token);

      $socialAccount = SocialAccount::where('provider_name', $provider)
        ->where('provider_id', $socialUser->getId())
        ->first();

      if ($socialAccount) {
        $user = $socialAccount->user;


        $user->tokens()->delete();
        $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

        return response()->json([
          'status' => 'Success',
          'user' => $user,
          'access_token' => $accessToken->plainTextToken,
          'refresh_token' => $refreshToken->plainTextToken,
        ], 200);
      }

      $user = User::where('email', $socialUser->getEmail())->first();

      if (!$user) {

        $user = User::create([
          'email' => $socialUser->getEmail(),
          'birthday' => $socialUser->getBirthday(),
          'phone' => $socialUser->getPhoneNumber(),
          'nickname' => $socialUser->getNickname() ?? $socialUser->getName(),
        ]);
        $user->userSetting()->create();
      }

      $user->socialAccounts()->create([
        'provider_name' => $provider,
        'provider_id' => $socialUser->getId(),
      ]);



      $user->tokens()->delete();
      $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
      $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

      return response()->json([
        'status' => 'Success',
        'user' => $user,
        'access_token' => $accessToken->plainTextToken,
        'refresh_token' => $refreshToken->plainTextToken,
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        'status' => 'Fail',
        'message' => 'Social Login Fail',
      ], 400);
    }
  }
}
