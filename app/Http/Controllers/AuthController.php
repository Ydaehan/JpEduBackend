<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
  /**
   * @OA\Post (
   *     path="/api/register",
   *     tags={"Auth"},
   *     summary="회원가입",
   *     description="회원을 등록
   *     따로 access token을 발급해 주지 않으므로 로그인창으로 넘어가게 구현하시면 됩니다.",
   *     @OA\RequestBody(
   *         description="회원 정보",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="application/json",
   *             @OA\Schema (
   *                 @OA\Property (property="nickname", type="string", description="회원 닉네임", example="testuser1"),
   *                 @OA\Property (property="name", type="string", description="회원 아이디", example="test1"),
   *                 @OA\Property (property="email", type="email", description="회원 이메일", example="testuser@naver.com"),
   *                 @OA\Property (property="password", type="string", description="회원 비밀번호", example="asdf1234"),
   *                 @OA\Property (property="password_confirmation", type="string", description="회원 비밀번호 확인", example="asdf1234"),
   *                 @OA\Property (property="phone", type="string", description="회원 전화번호", example="01012345678"),
   *                 @OA\Property (property="birthday", type="date", description="회원 생일", example="01/01/2000")
   *             )
   *         )
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function register(Request $request)
  {
    $validator = Validator::make($request->json()->all(), [
      'nickname' => 'required|string|max:255|unique:users',
      'email' => 'required|email|max:255|unique:users',
      'password' => 'required|string|min:6|max:255|confirmed',
      'phone' => 'required|string|max:15',
      'birthday' => 'required|date',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'messages' => $validator->messages()
      ], 400);
    }

    $user = User::create([
      'nickname' => $request->get('nickname'),
      'email' => $request->get('email'),
      'password' => Hash::make($request->get('password')),
      'phone' => $request->get('phone'),
      'birthday' => $request->get('birthday'),
    //   'verification_code' => sha1(time())
    ]);

    if ($user) {
        // 이메일 전송
        // MailController::sendRegisterEmail($user->name, $user->email, $user->verification_code);
      return response()->json([
        'status' => 'Success.',
        'user' => $user
      ], 200);
    }
    return response()->json([
      'status' => 'error',
      'message' => 'failed to create user'
    ]);
  }

  /**
   * @OA\Post (
   *     path="/api/login",
   *     tags={"Auth"},
   *     summary="로그인",
   *     description="회원 로그인
   *     Access token 과 refresh token을 반환해주므로 적당히 저장해둔 후 각 요청의 Header부분에 넣어서 사용하시면 됩니다.",
   *     @OA\RequestBody(
   *         description="로그인 정보",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="application/json",
   *             @OA\Schema (
   *                 @OA\Property (property="name", type="string", description="회원 아이디", example="test1"),
   *                 @OA\Property (property="password", type="string", description="회원 비밀번호", example="asdf1234")
   *             )
   *         )
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
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

    if (DB::table('personal_access_tokens')->where('tokenable_id',$user->id)->exists()){
        return response()->json([
            'status' => 'error',
            'message' => '이미 로그인되어 있습니다. 로그아웃 후 다시 시도하세요.'
        ]);
    }

    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
      $user = Auth::user();

      $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
      $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

      return response()->json([
        'status' => 'Success',
        'user' => $user,
        'access_token' => $accessToken->plainTextToken,
        'refresh_token' => $refreshToken->plainTextToken,
      ], 200);
    }

    return response()->json([
      'status' => 'error',
      'messages' => 'invalid credentials'
    ]);
  }

  /**
   * @OA\Post (
   *     path="/api/logout",
   *     tags={"Auth"},
   *     summary="로그아웃",
   *     description="회원 로그아웃
   *     로그아웃 시 해당 로그인 유저와 관련된 토큰이 모두 삭제되므로 가지고 있는 Access token과 refresh token은 폐기하시면 됩니다.",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function logout()
  {
    auth('sanctum')->user()->tokens()->delete();
    return response()->json([
      'status' => 'Success',
      'message' => 'Logout success'
    ]);
  }

  /**
   * @OA\Post (
   *     path="/api/refresh",
   *     tags={"Auth"},
   *     summary="ACCESS_TOKEN 재발급",
   *     description="회원 ACCESS_TOKEN 재발급
   *     회원의 Access token이 만료되었을 경우 refresh token을 헤더에 담아 보내면 새로운 Access token이 발급됩니다.
   *     front 측에서는 발급된 Access token을 저장해두면 됩니다.",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {refresh_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function refreshToken()
  {
    $user = auth('sanctum')->user();
    $oldAccessToken = $user->tokens->where('name', 'API Token')->first();
    if ($oldAccessToken) {
      $oldAccessToken->delete();
    }
    $accessToken = auth('sanctum')->user()->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
    return response(['message' => "Token generate", 'token' => $accessToken->plainTextToken]);
  }

  /**
   * @OA\Delete (
   *     path="/api/sign-out",
   *     tags={"Auth"},
   *     summary="회원 탈퇴",
   *     description="회원 탈퇴
   *     로그인 된 유저와 같은 회원정보를 삭제합니다.",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   *
   * */
  public function signOut()
  {
    $user = auth('sanctum')->user();
    $user->tokens()->delete();
    $user->delete();
    return response()->json([
      'status' => 'Success',
      'message' => 'User delete success'
    ]);
  }

  // 이메일 인증때 필요한 코드
  public function verifyUser(Request $request)
  {
    $verification_code = \Illuminate\Support\Facades\Request::get('code');
    $user = User::where(['verification_code' => $verification_code])->first();
    if($user != null){
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
}
