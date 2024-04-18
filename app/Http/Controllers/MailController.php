<?php

namespace App\Http\Controllers;

use App\OpenApi\Responses\StoreSuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Mail\RegisterEmail;
use App\Models\ManagerWaitList;
use App\OpenApi\RequestBodies\ApplyToManagerRequestBody;
use App\OpenApi\RequestBodies\SendSignUpEmailRequestBody;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\ErrorValidationResponse;
use App\OpenApi\Responses\SuccessResponse;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class MailController extends Controller
{
    /**
     * 매니저 지원
     *
     * 매니저 지원자가 이메일과 지원사유를 적어 보내면 지원신청이 됩니다.
     * */
    #[OpenApi\Operation(tags: ['Mail'], method: 'POST')]
    #[OpenApi\RequestBody(factory: ApplyToManagerRequestBody::class)]
    #[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
    #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
    #[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
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
            return response()->json(['message' => '신청 중 오류가 발생했습니다.', 'error' => $e->getMessage()]);
        }
    }

    /**
     * 매니저 지원 대기자 이메일 발송
     *
     * 관리자가 지원 대기자를 승인할 경우 해당 지원내용의 id와 함께 요청을 보내면<br/>
     * 해당 지원자에게 메일을 발송합니다.
     * */
    #[OpenApi\Operation(tags: ['Mail'], method: 'POST')]
    #[OpenApi\RequestBody(factory: SendSignUpEmailRequestBody::class)]
    #[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
    #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
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
