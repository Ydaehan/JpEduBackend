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
          'status' => 'success',
          'user' => $user
        ], 200);
      }
      return response()->json([
        'status' => 'error',
        'message' => 'failed to create user'
      ]);
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

      if (Auth::attempt(['name' => $request->name, 'password' => $request->password])) {
        $user = Auth::user();

        $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

        return response()->json([
            'status' => 'success',
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

    public function logout()
    {
        auth('sanctum')->user()->tokens()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Logout success'
        ]);
    }

    public function refreshToken ()
    {
        dd(auth('sanctum')->user());
        $accessToken = auth('sanctum')->user()->createToken('access_token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        return response(['message' => "Token generate", 'token' => $accessToken->plainTextToken]);
    }
}
