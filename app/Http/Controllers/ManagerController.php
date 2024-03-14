<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Manager;
use App\Models\ManagerWaitList;
use Laravel\Sanctum\PersonalAccessToken;

class ManagerController extends Controller
{

  /**
   * @OA\Post (
   *     path="/api/manager/register",
   *     tags={"Manager"},
   *     summary="매니저 회원가입",
   *     description="매니저를 등록
   *     URL에 토큰과 email이 넣어져 있기 때문에 회원가입창의 email 부분을 해당 이메일로 넣어주기
   *     따로 access token을 발급해 주지 않으므로 로그인창으로 넘어가게 구현하시면 됩니다.",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\RequestBody(
   *         description="매니저 정보",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="application/json",
   *             @OA\Schema (
   *                 @OA\Property (property="nickname", type="string", description="매니저 닉네임", example="testuser1"),
   *                 @OA\Property (property="name", type="string", description="매니저 아이디", example="test1"),
   *                 @OA\Property (property="email", type="email", description="매니저 이메일", example="testuser@naver.com"),
   *                 @OA\Property (property="password", type="string", description="매니저 비밀번호", example="asdf1234"),
   *                 @OA\Property (property="password_confirmation", type="string", description="매니저 비밀번호 확인", example="asdf1234"),
   *                 @OA\Property (property="phone", type="string", description="매니저 전화번호", example="01012345678"),
   *                 @OA\Property (property="birthday", type="date", description="매니저 생일", example="01/01/2000")
   *             )
   *         )
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  // 매니저 회원가입
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
      $manager = Manager::create([
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
   * @OA\Delete (
   *     path="/api/manager/deleteManagerWaitList/{email}",
   *     tags={"Manager"},
   *     summary="매니저 대기 명단 거부 및 DB에서 삭제",
   *     description="관리자가 매니저 대기 명단을 거부 했을 경우
   *     해당 지원자의 이메일을 함께 보내면 삭제됩니다.
   *     아직 미완성 입니다.",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Parameter(
   *         name="email",
   *         in="path",
   *         required=true,
   *         description="applicantEmail@gmail.com",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   *
   * */
  public function deleteManagerWaitList(string $email)
  {
    $applicant = ManagerWaitList::where('email', $email)->first();
    try {
      $applicant->delete();
      return response()->json(['message' => '매니저 지원이 거부 되었습니다.'], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => '매니저 지원 거부 중 오류가 발생했습니다.']);
    }
  }
}
