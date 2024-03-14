<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Mail\RegisterEmail;
use App\Models\ManagerWaitList;

class MailController extends Controller
{
  /**
   * @OA\Post (
   *     path="/api/mail/applyToManager",
   *     tags={"Manager"},
   *     summary="매니저 지원",
   *     description="매니저 지원자가
   *     이메일과 지원사유를 적어 보내면 지원신청이 됩니다.",
   *     @OA\RequestBody(
   *         description="매니저 정보",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="application/json",
   *             @OA\Schema (
   *                 @OA\Property (property="email", type="string", description="신청자 이메일", example="testuser1"),
   *                 @OA\Property (property="content", type="string", description="지원 사유", example="{{ 설명글 }}"),
   *             )
   *         )
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  // 매니저 지원
  public function applyToManager(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|max:255|unique:users|unique:manager_wait_lists|unique:managers',
      'content' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'messages' => $validator->messages()
      ], 400);
    }

    try {
      $applicant = ManagerWaitList::create([
        'email' => $request->email,
        'content' => $request->content,
      ]);

      return response()->json(['message' => '신청 되었습니다.'], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => '신청 중 오류가 발생했습니다.']);
    }
  }

  /**
   * @OA\Post (
   *     path="/api/mail/sendSignUpEmail/{email}",
   *     tags={"Manager"},
   *     summary="매니저 지원 대기자 이메일 발송",
   *     description="관리자가
   *     지원 대기자를 승인할 경우 해당 지원내용의 id와 함께 요청을 보내면
   *     해당 지원자에게 메일을 발송합니다.",
   *     @OA\Parameter(
   *         name="email",
   *         in="path",
   *         required=true,
   *         description="매니저 지원자의 email",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\RequestBody(
   *         description="매니저 지원자의 이메일 및 지원 사유",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="application/json",
   *             @OA\Schema (
   *                 @OA\Property (property="email", type="string", description="신청자 이메일", example="testuser1"),
   *                 @OA\Property (property="content", type="string", description="지원 사유", example="{{ 지원 사유 }}"),
   *             )
   *         )
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  // 매니저 지원 대기자 이메일 발송
  public function sendSignUpEmail(string $email)
  {
    try {
      $user = ManagerWaitList::where('email', $email)->first();
      $token = $user->createToken('SignUpToken', ['manager'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
      Mail::to($user->email)->send(new RegisterEmail($token->plainTextToken, $user->email)); // token 넣어야함

      return response()->json(['message' => '이메일이 성공적으로 발송되었습니다.: ' . $token->plainTextToken], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => '이메일 발송 중 오류가 발생했습니다.: ' . $e->getMessage()]);
    }
  }
}
