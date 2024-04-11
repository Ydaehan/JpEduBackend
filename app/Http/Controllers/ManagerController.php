<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ManagerWaitList;
use App\OpenApi\Parameters\AccessTokenParameters;
use App\OpenApi\RequestBodies\DeleteWaitListRequestBody;
use App\OpenApi\RequestBodies\ManagerStoreRequestBody;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\ErrorValidationResponse;
use App\OpenApi\Responses\StoreSuccessResponse;
use App\OpenApi\Responses\SuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use Laravel\Sanctum\PersonalAccessToken;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class ManagerController extends Controller
{
	/**
	 * 매니저 회원가입
	 *
	 * 매니저를 등록<br/>
	 * URL에 토큰과 email이 넣어져 있기 때문에 회원가입창의 email 부분을 해당 이메일로 넣어주기<br/>
	 * 따로 access token을 발급해 주지 않으므로 로그인창으로 넘어가게 구현하시면 됩니다.
	 */
	#[OpenApi\Operation(tags: ['Manager'], method: 'POST')]
	#[OpenApi\Parameters(factory: AccessTokenParameters::class)]
	#[OpenApi\RequestBody(factory: ManagerStoreRequestBody::class)]
	#[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 오류', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
	#[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
	public function managerSignUp(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'nickname' => 'required|string|max:255|unique:users',
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|string|min:6|max:255|confirmed',
			'phone' => 'required|numeric|digits_between:11,15',
			'birthday' => 'required|date',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status' => 'error',
				'messages' => $validator->messages()
			], 400);
		}

		try {
			$manager = User::create([
				'nickname' => $request->get('nickname'),
				'email' => $request->get('email'),
				'password' => Hash::make($request->get('password')),
				'phone' => $request->get('phone'),
				'birthday' => $request->get('birthday'),
				'role' => 'manager',
			]);

			$authorizationHeader = $request->header('Authorization');
			$token = str_replace('Bearer ', '', $authorizationHeader);

			PersonalAccessToken::findToken($token)->delete(); // 매니저 회원가입 인증용 토큰 삭제
			ManagerWaitList::where('email', $request->get('email'))->delete(); // 회원가입 후 지원 대기열에서 삭제

			return response()->json(['message' => '매니저 가입 성공'], 200);
		} catch (\Exception $e) {
			return response()->json(['message' => '생성 중 오류가 발생했습니다 : ' . $e->getMessage()]);
		}
	}

	/**
	 * 매니저 대기 명단 거부 및 DB에서 삭제
	 *
	 * 관리자가 매니저 대기 명단을 거부 했을 경우<br/>
	 * 해당 지원자의 이메일을 함께 보내면 삭제됩니다.
	 * */
	#[OpenApi\Operation(tags: ['Manager'], method: 'DELETE')]
	#[OpenApi\Parameters(factory: AccessTokenParameters::class)]
	#[OpenApi\RequestBody(factory: DeleteWaitListRequestBody::class)]
	#[OpenApi\Response(factory: SuccessResponse::class, description: '매니저 지원 거부 성공', statusCode: 200)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 오류', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
	public function deleteManagerWaitList(Request $request)
	{
		$email = $request->input('email');
		$applicant = ManagerWaitList::where('email', $email)->first();
		try {
			$applicant->delete();
			return response()->json(['message' => '매니저 지원이 거부 되었습니다.'], 200);
		} catch (\Exception $e) {
			return response()->json(['message' => '매니저 지원 거부 중 오류가 발생했습니다.']);
		}
	}
}
