<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\error;

class AuthController extends Controller
{   
  public function register(Request $request)
  {
    $validator = Validator::make($request->json()->all(), [
      'nickname' => 'required|string|max:255',
      'name' => 'required|string|max:255',
      'email' => 'required|email|max:255|unique:users',
      'password' => 'required|string|min:6|max:255|confirmed',
      'password_confirmation' => 'required|string|min:6|max:255',
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
    
    return response()->json([
      'status' => 'success',
      'user' => $user,
    ], 201);
  }
  
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

    // 회원 정보 저장
    $credentials = request(['name', 'password']);
    
    // access token 생성
    if(! $token = auth()->attempt($credentials)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
    // refresh token 생성
    if(! $refreshToken = auth()->setTTL(config('jwt.refresh_ttl'))->attempt($credentials)){
      return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    return $this->respondWithToken($token, $refreshToken);
  }

  protected function respondWithToken($token, $refreshToken) { 
    return response()->json([
      'access_token' => $token,
      'token_type' => 'bearer',
      'expires_in' => config('jwt.ttl') * 60,
      'refresh_token' => $refreshToken,
      'refresh_type' => 'bearer',
      'refresh_expires_in' => config('jwt.refresh_ttl') * 60,
    ]);
  }
  
  public function user() {
    return response()->json(Auth::guard('api')->user());
  }


  public function refresh() {
    $refreshToken = null;
    if(isset($_SERVER['HTTP_AUTHORIZATION'])) {
      list($bearer,$token) = explode(' ', $_SERVER['HTTP_AUTHORIZATION'], 2);

      if(strcasecmp($bearer, 'Bearer') === 0) {
        $refreshToken = $token;
      }
    }
    return $this->respondWithToken(Auth::guard('api')->refresh(),$refreshToken);
  }
  
  public function logout(Request $request) {
    // refresh token 무효화 (구현중)
    $refreshToken = $request->input('refresh_token');

    if(auth()->setTTL(config('jwt.refresh_ttl'))->invalidate($refreshToken)){
      return response()->json([
        'status' => 'success',
        'message' => 'logout'
      ]);
    }else{
      return response()->json([
        'error' => 'refresh token is required'
      ]);
    }

    auth()->logout();

    return response()->json([
      'status' => 'success',
      'message' => 'logout'
    ],200);
  }
}
