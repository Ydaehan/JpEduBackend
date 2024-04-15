<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\OpenApi\Parameters\AccessTokenParameters;
use App\OpenApi\RequestBodies\LoginRequestBody;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
use App\OpenApi\RequestBodies\UserStoreRequestBody;
use App\OpenApi\RequestBodies\UserUpdateRequestBody;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\ErrorValidationResponse;
use App\OpenApi\Responses\LoginResponse;
use App\OpenApi\Responses\LogoutResponse;
use App\OpenApi\Responses\StoreSuccessResponse;
use App\OpenApi\Responses\SuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use App\OpenApi\Responses\UserResponse;

#[OpenApi\PathItem]
class AuthController extends Controller
{
  /**
   * 회원가입
   *
   * 회원의 닉네임,이름,비밀번호,비밀번호 확인 <br/>
   * 전화번호, 생일, 역할을 입력받아 <br/>
   * 유효성 검사를 진행하고 통과 시 회원가입 절차를 진행합니다. <br/>
   * role 부분을 제외한 모든 데이터는 필수요소입니다.<br/>
   * manager, admin은 role 부분에 Manager, Admin 데이터를 기입해 주셔야 합니다. <br/>
   * 일반 사용자의 경우 role 부분을 보내지 않아도 default로 User 값이 들어가게 됩니다. <br/>
   */
  #[OpenApi\Operation(tags: ['User'], method: 'POST')]
  #[OpenApi\RequestBody(factory: UserStoreRequestBody::class)]
  #[OpenApi\Response(factory: UserResponse::class, description: '회원가입 성공', statusCode: 201)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '서버요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
  public function register(Request $request)
  {
    $validator = Validator::make($request->json()->all(), [
      'nickname' => 'required|string|max:255',
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

    $validatedData = $validator->validated();
    $user = User::create([
      'nickname' => $validatedData['nickname'],
      'email' => $validatedData['email'],
      'password' => Hash::make($validatedData['password']),
      'phone' => $validatedData['phone'],
      'birthday' => $validatedData['birthday'],
      // 'verification_code' => sha1(time())
    ]);

    if (isset($validatedData['role']) && $validatedData['role'] !== null) {
      $user->role = $validatedData['role'];
      $user->save();
    }

    if ($user) {
      // 이메일 전송
      // MailController::sendRegisterEmail($user->name, $user->email, $user->verification_code);
      $user->userSetting()->create();
      return response()->json([
        'status' => 'Success.',
        'message' => 'User created successfully'
      ], 201);
    }
    return response()->json([
      'status' => 'error',
      'message' => 'failed to create user'
    ]);
  }


  /**
   * 로그인
   *
   * 회원의 이메일과 비밀번호를 검사하고, 로그인 합니다. <br/>
   * 로그인 시 AccessToken 과 RefreshToken을 발급해 주며 <br/>
   * AccessToken은 API요청을, RefreshToken은 AccessToken을 재발급 할 때 사용합니다. <br/>
   */
  #[OpenApi\Operation(tags: ['User'], method: 'POST')]
  #[OpenApi\RequestBody(factory: LoginRequestBody::class)]
  #[OpenApi\Response(factory: LoginResponse::class, description: '로그인 성공', statusCode: 201)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '서버요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|max:255',
      'password' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'messages' => $validator->messages()
      ], 400);
    }
    $user = User::where('email', $request->email)->first();
    $user->tokens()->delete();
    if (DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->exists()) {
      return response()->json([
        'status' => 'error',
        'message' => '이미 로그인되어 있습니다. 로그아웃 후 다시 시도하세요.'
      ]);
    }

    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
      /** @var \App\Models\User $user **/
      $user = Auth::user();
      return createTokensAndRespond($user);
    }

    return response()->json([
      'status' => 'error',
      'messages' => 'invalid credentials'
    ]);
  }

  /**
   * 로그아웃
   *
   * 회원의 로그아웃을 진행합니다. <br/>
   * 로그인 된 유저와 같은 회원정보를 삭제합니다.
   * */
  #[OpenApi\Operation(tags: ['User'], method: 'DELETE')]
  #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
  #[OpenApi\Response(factory: LogoutResponse::class, description: '로그아웃 성공', statusCode: 200)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '서버요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '토큰 인증 실패', statusCode: 401)]
  public function logout()
  {
    /** @var \App\Models\User $user **/
    $user = auth('sanctum')->user();
    $user->tokens()->delete();
    return response()->json([
      'status' => 'Success',
      'message' => 'Logout success'
    ]);
  }

  /**
   * ACCESS_TOKEN 재발급
   *
   * 회원의 Access token이 만료되었을 경우 refresh token을 헤더에 담아 보내면 새로운 Access token이 발급됩니다. <br/>
   * front 측에서는 발급된 Access token을 저장해두면 됩니다.
   */
  #[OpenApi\Operation(tags: ['User'], method: 'POST')]
  #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
  #[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '서버요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '토큰 인증 실패', statusCode: 401)]
  public function refreshToken()
  {
    /** @var \App\Models\User $user **/
    $user = auth('sanctum')->user();
    $oldAccessToken = $user->tokens->where('name', 'API Token')->first();
    if ($oldAccessToken) {
      $oldAccessToken->delete();
    }
    $role = $user->role;
    $accessToken = $user->createToken('API Token', [$role], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
    return response(['message' => "Token generate", 'token' => $accessToken->plainTextToken]);
  }

  /**
   *	회원 탈퇴
   *
   *	로그인 된 유저와 같은 회원정보를 삭제합니다.
   *	회원의 토큰을 삭제하고 회원정보를 삭제합니다.
   */
  #[OpenApi\Operation(tags: ['User'], method: 'DELETE')]
  #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
  #[OpenApi\Response(factory: SuccessResponse::class, description: '회원 탈퇴 성공', statusCode: 201)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '서버요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '토큰 인증 실패', statusCode: 401)]
  public function signOut()
  {
    /** @var \App\Models\User $user **/
    $user = auth('sanctum')->user();
    $user->tokens()->delete();
    $user->delete();
    return response()->json([
      'status' => 'Success',
      'message' => 'User delete success'
    ]);
  }

  /**
   * 회원 정보 수정
   *
   * 회원의 닉네임, 이메일, 비밀번호, 비밀번호 confirmed, 전화번호, 생일을 확인하여 DB에 저장한다 <br/>
   * 비밀번호는 비밀번호 confirmed와 같아야 하며 최소 길이 6 이상
   * 전화번호는 11~15자 이내로 작성해야한다
   * 생일은 date 양식으로 보내야한다.
   * */

  #[OpenApi\Operation(tags: ['User'], method: 'PATCH')]
  #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
  #[OpenApi\RequestBody(factory: UserUpdateRequestBody::class)]
  #[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '서버요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '토큰 인증 실패', statusCode: 401)]
  #[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
  public function update(Request $request)
  {
    /** @var \App\Models\User $user **/
    $user = auth('sanctum')->user();
    $validator = Validator::make($request->all(), [
      'nickname' => 'string|max:255',
      'email' => 'email|max:255|unique:users',
      'password' => 'string|min:6|max:255|confirmed',
      'phone' => 'numeric|digits_between:11,15',
      'birthday' => 'date',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'messages' => $validator->messages()
      ], 400);
    }

    $user->update($request->all());

    return response()->json([
      'status' => 'Success',
      'message' => 'User update success'
    ]);
  }

  // 이메일 인증때 필요한 코드
  public function verifyUser(Request $request)
  {
    $verification_code = \Illuminate\Support\Facades\Request::get('code');
    $user = User::where(['verification_code' => $verification_code])->first();
    if ($user != null) {
      $user->is_verified = 1;
      $user->save();
      return response()->json([
        'status' => 'Success',
        'message' => 'User verify success'
      ]);
    }

    return response()->json([
      'status' => 'Fail',
      'message' => 'User verify fail'
    ]);
  }

  protected function createTestToken(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|max:255',
      'password' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'messages' => $validator->messages()
      ], 400);
    }
    $user = User::where('email', $request->email)->first();
    $user->tokens()->delete();

    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
      /** @var \App\Models\User $user **/
      $user = Auth::user();
      $role = $user->role;
      $accessToken = $user->createToken('API Token', [$role], Carbon::now()->addMinutes(config('sanctum.test_expiration')));
      return response()->json([
        'status' => 'Success',
        'user' => $user,
        'access_token' => $accessToken->plainTextToken,
      ]);
    }

    return response()->json([
      'status' => 'error',
      'messages' => 'invalid credentials'
    ]);
  }
}
