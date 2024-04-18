<?php

namespace App\Http\Controllers;

use App\OpenApi\Parameters\S3Parameters;
use App\OpenApi\RequestBodies\S3RequestBody;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\ErrorValidationResponse;
use App\OpenApi\Responses\StoreSuccessResponse;
use App\OpenApi\Responses\SuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class S3Controller extends Controller
{
  /**
   * s3 파일 전체 URL 가져오기
   *
   * s3에 저장된 파일들의 전체 URL을 가져옵니다.<br/>
   * path를 보내면 해당 경로의 파일들의 URL을 가져옵니다.<br/>
   * path는 s3의 경로를 의미합니다.<br/>
   * path는 mobile-images, web-images, user-files 중 1개를 선택하여 보내야 합니다.
   */
  #[OpenApi\Operation(tags: ['S3'], method: 'GET')]
  #[OpenApi\Parameters(factory: S3Parameters::class)]
  #[OpenApi\Response(factory: SuccessResponse::class, description: '성공', statusCode: 200)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
  public function getS3Files(Request $request)
  {
    try {
      $validated = $request->validate([
        'path' => 'required|string',
      ]);
      $files = Storage::disk('s3')->files($validated['path']);
      return response()->json(['files' => $files]);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()]);
    }
  }

  /**
   * s3 파일 업로드
   *
   * s3에 파일을 업로드합니다.<br/>
   * 파일을 업로드할 때는 파일과 경로를 함께 보내야 합니다.<br/>
   * 파일은 file, 경로는 path로 보내야 합니다.<br/>
   * path는 achievements, grammars, mobile-images, web-images, user-files 중 1개를 선택하여 보내야 합니다.
   */
  #[OpenApi\Operation(tags: ['S3'], method: 'POST')]
  #[OpenApi\RequestBody(factory: S3RequestBody::class)]
  #[OpenApi\Response(factory: StoreSuccessResponse::class, description: 'S3 파일 업로드 성공', statusCode: 201)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
  #[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
  public function store(Request $request)
  {
    $validated = $request->validate([
      'file' => 'required|file',
      'path' => 'required|string',
    ]);
    // name duplicate check
    if ($this->checkDuplicateFileName($validated['file'], $validated['path'])) {
      return response()->json(['error' => '중복된 이름의 파일이 존재합니다. 파일의 이름을 다른 이름으로 저장해주세요.']);
    }
    // s3 upload
    $result = s3Upload($validated['file'], $validated['path']);
    $fileUrl = getS3GetUrl($result);
    return response()->json(['url' => $fileUrl]);
  }

  public function destroy(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string',
      'path' => 'required|string',
    ]);
    $result = $this->s3Delete($validated['name'], $validated['path']);
    return response()->json(['result' => $result]);
  }

  private function checkDuplicateFileName($file, $path)
  {
    $files = Storage::disk('s3')->files($path);
    foreach ($files as $f) {
      if ($file->getClientOriginalName() === basename($f)) {
        return true;
      }
    }
  }

  private function s3Delete($name, $path)
  {
    return Storage::disk('s3')->delete($path . "/" . $name);
  }
}
