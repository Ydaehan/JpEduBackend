<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

use App\Models\SocialAccount;
use App\Models\User;
use Carbon\Carbon;


class SocialController extends Controller
{
  //

  public function login(string $provider)
  {
    if (!array_key_exists($provider, config('services'))) {
      return redirect('login')->with('error', $provider . ' 지원하지 않는 서비스입니다.');
    }
    return Socialite::driver($provider)->redirect();
  }


  public function callback(string $provider)
  {

    try {

      $socialUser = Socialite::driver($provider)->user();

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
          'name' => $socialUser->getId(),
          'nickname' => $socialUser->getName() ? $socialUser->getName() : $socialUser->getNickname(),
          // 'profile_image' => $socialUser->getAvatar(),
          'email_verified_at' => now(),
        ]);
      }

      // 소셜 계정 생성
      $user->socialAccounts()->create([
        'provider_name' => $provider,
        'provider_id' => $socialUser->getId(),
        'nickname' => $socialUser->getNickname(),
        'email' => $socialUser->getEmail(),
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
        'message' => 'Social Login Fail' . $e->getMessage(),
      ]);
    }
  }
}
