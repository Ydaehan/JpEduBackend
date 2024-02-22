<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
        /**
     * @OA\Post (
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="회원가입",
     *     description="회원을 등록",
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
        'nickname' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users',
        'password' => 'required|string|min:6|max:255|confirmed',
        'phone' => 'required|string|max:15',
        'birthday' => 'required|date',
      ]);

      if($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'messages' => $validator->messages()
        ], 200);
      }

      $user = User::create([
        'nickname' => $request->get('nickname'),
        'name' => $request->get('name'),
        'email' => $request->get('email'),
        'password' => Hash::make($request->get('password')),
        'phone' => $request->get('phone'),
        'birthday' => $request->get('birthday'),
      ]);

      if($user){
        return response()->json([
          'status' => 'Success',
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
     *     description="회원 로그인",
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
    public function login(Request $request) {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'password' => 'required|string|min:6',
      ]);

      if($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'messages' => $validator->messages()
        ],200);
      }

      if (Auth::attempt(['name' => $request->name, 'password' => $request->password])) {
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
     *     description="회원 로그아웃",
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
     *     description="회원 ACCESS_TOKEN 재발급",
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
    public function refreshToken ()
    {
        $accessToken = auth('sanctum')->user()->createToken('access_token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        return response(['message' => "Token generate", 'token' => $accessToken->plainTextToken]);
    }
}
