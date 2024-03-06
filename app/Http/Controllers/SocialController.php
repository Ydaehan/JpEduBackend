<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Models\SocialAccount;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class SocialController extends Controller
{
  //
  /**
   * @OA\Get (
   *     path="/api/social/{provider}",
   *     tags={"SocialAuth"},
   *     summary="소셜 로그인",
   *     description="소셜 회원 로그인",
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
      try {
        $socialUser = Socialite::driver($provider)->stateless()->user();
      } catch (Exception $e) {
        return response()->json(['error' => 'Invalid credentials provided.'], 422);
      }

      $socialAccount = SocialAccount::where('provider_name', $provider)
        ->where('provider_id', $socialUser->getId())
        ->first();

      if ($socialAccount) {
        //
        $user = $socialAccount->user;
        Auth::login($user);

        $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));


        return response()->json([
          'status' => 'Success',
          'user' => $user,
          'access_token' => $accessToken->plainTextToken,
          'refresh_token' => $refreshToken->plainTextToken,
        ], 200);
      }

      // Find User
      $user = User::where('email', $socialUser->getEmail())->first();


      if (!$user) {
        $user = User::create([
          'email' => $socialUser->getEmail(),
          'birthday' => $socialUser->birthday ? $socialUser->birthday : null,
          'phone' => $socialUser->phoneNumber ? $socialUser->phoneNumber : null,
          'nickname' => $socialUser->getNickname() ? $socialUser->getNickname() : $socialUser->getName(),
          'email_verified_at' => now(),
        ]);
      }

      // 소셜 계정 생성
      $user->socialAccounts()->create([
        'provider_name' => $provider,
        'provider_id' => $socialUser->getId(),
      ]);

      // 토큰 발행 후 로그인
      Auth::login($user);
      $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
      $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

      return response()->json([
        'status' => 'Success',
        'user' => $user,
        'access_token' => $accessToken->plainTextToken,
        'refresh_token' => $refreshToken->plainTextToken,
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'Fail',
        'message' => 'Social Login Fail: ' . $e->getMessage(),
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
   *     @OA\RequestBody(
   *         description="provider access_token",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="application/json",
   *             @OA\Schema (
   *                 @OA\Property (property="token", type="string", description="provider access_token", example="access_token")
   *             )
   *         )
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function mobileCallback(string $provider, Request $request)
  {
    try {
      try {
        $socialUser = Socialite::driver($provider)->userFromToken($request->token);
      } catch (Exception $e) {
        return response()->json(['error' => 'Invalid credentials provided.'], 422);
      }

      $socialAccount = SocialAccount::where('provider_name', $provider)
        ->where('provider_id', $socialUser->getId())
        ->first();

      if ($socialAccount) {
        //
        $user = $socialAccount->user;
        Auth::login($user);

        $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));


        return response()->json([
          'status' => 'Success',
          'user' => $user,
          'access_token' => $accessToken->plainTextToken,
          'refresh_token' => $refreshToken->plainTextToken,
        ], 200);
      }

      // Find User
      $user = User::where('email', $socialUser->getEmail())->first();


      if (!$user) {
        $user = User::create([
          'email' => $socialUser->getEmail(),
          'birthday' => $socialUser->birthday ? $socialUser->birthday : null,
          'phone' => $socialUser->phoneNumber ? $socialUser->phoneNumber : null,
          'nickname' => $socialUser->getNickname() ? $socialUser->getNickname() : $socialUser->getName(),
          'email_verified_at' => now(),
        ]);
      }

      // 소셜 계정 생성
      $user->socialAccounts()->create([
        'provider_name' => $provider,
        'provider_id' => $socialUser->getId(),
      ]);

      // 토큰 발행 후 로그인
      Auth::login($user);
      $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
      $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

      return response()->json([
        'status' => 'Success',
        'user' => $user,
        'access_token' => $accessToken->plainTextToken,
        'refresh_token' => $refreshToken->plainTextToken,
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'Fail',
        'message' => 'Social Login Fail: ' . $e->getMessage(),
      ], 400);
    }
  }
}
