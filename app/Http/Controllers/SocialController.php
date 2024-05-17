<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Models\SocialAccount;
use App\Models\User;
use App\OpenApi\Parameters\MobileSocialCallbackParameters;
use App\OpenApi\Parameters\SocialCallbackParameters;
use App\OpenApi\Parameters\SocialLoginParameters;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\SuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use Exception;
use Illuminate\Support\Facades\Validator;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;


#[OpenApi\PathItem]
class SocialController extends Controller
{

	// 소셜 로그인 처리 메서드
	private function handleSocialUser(string $provider, $socialUser)
	{
		$socialAccount = SocialAccount::where('provider_name', $provider)
			->where('provider_id', $socialUser->getId())
			->first();

		if ($socialAccount) {
			Auth::login($socialAccount->user);
			return createTokensAndRespond($socialAccount->user);
		}

		$user = User::where('email', $socialUser->getEmail())->first();


		if (!$user) {
			$user = User::create([
				'email' => $socialUser->getEmail(),
				'nickname' => $socialUser->getNickname() ?? $socialUser->getName(),
				// 'birthday' => ,
				// 'phone' =>
			]);
			$user->userSetting()->create();
		}


		$user->socialAccounts()->create([
			'provider_name' => $provider,
			'provider_id' => $socialUser->getId(),
		]);
		Auth::login($user);
		return createTokensAndRespond($user);
	}

	/**
	 * 소셜 로그인 URL 생성
	 *
	 * 소셜 로그인 URL을 생성합니다.
	 */
	#[OpenApi\Operation(tags: ['SocialAuth'], method: 'GET')]
	#[OpenApi\Parameters(factory: SocialLoginParameters::class)]
	#[OpenApi\Response(factory: SuccessResponse::class, description: '요청 성공', statusCode: 200)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 오류', statusCode: 400)]
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
	 * 소셜 로그인 콜백 처리
	 *
	 * 소셜 로그인 콜백 처리
	 */
	#[OpenApi\Operation(tags: ['SocialAuth'], method: 'GET')]
	#[OpenApi\Parameters(factory: SocialCallbackParameters::class)]
	#[OpenApi\Response(factory: SuccessResponse::class, description: '소셜 로그인 콜백 성공', statusCode: 200)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 오류', statusCode: 400)]
	public function callback(string $provider)
	{
		try {
			$socialUser = Socialite::driver($provider)->stateless()->user();
			return $this->handleSocialUser($provider, $socialUser);
		} catch (Exception $e) {
			return response()->json([
				'status' => 'Fail',
				'message' => 'SocialController: ' . $e->getMessage(),
			], 400);
		}
	}

	/**
	 * 모바일 소셜 로그인 콜백 처리
	 *
	 * 모바일 소셜 로그인 콜백 처리
	 */
	#[OpenApi\Operation(tags: ['SocialAuth'], method: 'GET')]
	#[OpenApi\Parameters(factory: MobileSocialCallbackParameters::class)]
	#[OpenApi\Response(factory: SuccessResponse::class, description: '모바일 소셜 로그인 콜백 성공', statusCode: 200)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 오류', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 오류', statusCode: 401)]
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
				'message' => 'SocialController: ' . $e->getMessage(),
			], 400);
		}
	}
}
