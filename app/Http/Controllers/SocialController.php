<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Models\SocialAccount;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;

class SocialController extends Controller
{

  // 사용자 토큰을 생성, 응답을 반환하는 메서드
  private function createTokensAndRespond(User $user)
  {
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

  // 사용자의 생일을 반환하는 메서드
  private function getBirthday(string $provider, $socialUser)
  {
    if ($provider === 'kakao') {
      $birthday = $socialUser->user['kakao_account']['birthday'];
      return $birthday;
    } else if ($provider === 'naver') {
      $birthday = $socialUser->user['response']['birthday'];
      return $birthday;
    } else {
      $birthday = $socialUser->user['birthday'];
      return $birthday;
    }
  }
  // 사용자의 전화번호를 반환하는 메서드
  private function getPhoneNumber(string $provider, $socialUser)
  {
    if ($provider === 'kakao') {

      $phoneNumber = $socialUser->user['kakao_account']['phone_number'];
      return $phoneNumber;
    } else if ($provider === 'naver') {
      $phoneNumber = $socialUser->user['response']['mobile'];
      return $phoneNumber;
    } else {
      $phoneNumber = $socialUser->user['phone'];
      return $phoneNumber;
    }
  }

  // 소셜 로그인 처리 메서드
  private function handleSocialUser(string $provider, $socialUser)
  {
    $socialAccount = SocialAccount::where('provider_name', $provider)
      ->where('provider_id', $socialUser->getId())
      ->first();

    if ($socialAccount) {
      return $this->createTokensAndRespond($socialAccount->user);
    }

    $user = User::where('email', $socialUser->getEmail())->first();

    if (!$user) {
      $user = User::create([
        'email' => $socialUser->getEmail(),
        'nickname' => $socialUser->getNickname() ?? $socialUser->getName(),
        'birthday' => $this->getBirthday($provider, $socialUser),
        'phone' => $this->getPhoneNumber($provider, $socialUser),
      ]);
      $user->userSetting()->create();
    }

    $user->socialAccounts()->create([
      'provider_name' => $provider,
      'provider_id' => $socialUser->getId(),
    ]);

    return $this->createTokensAndRespond($user);
  }


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
   *     description="소셜 로그인 콜백 처리",
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
      return $this->handleSocialUser($provider, $socialUser);
    } catch (Exception $e) {
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
   *     summary="모바일 소셜 로그인 콜백 처리",
   *     description="모바일 소셜 로그인 콜백 처리",
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
      $validator = Validator::make($request->json()->all(), [
        'token' => 'required|string',
      ]);
      $token = $request->bearerToken();
      $socialUser = Socialite::driver($provider)->userFromToken($token);

      return $this->handleSocialUser($provider, $socialUser);
    } catch (Exception $e) {
      return response()->json([
        'status' => 'Fail',
        'message' => 'Social Login Fail: ' . $e->getMessage(),
      ], 400);
    }
  }
}
