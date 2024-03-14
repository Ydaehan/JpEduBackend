<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class AdminMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    // 관리자 로직 추가
    // 사용자가 관리자 권한을 가지고 있는지 확인
    if ($request->user() && ($request->user()->role === 'manager' || $request->user()->role === 'admin')) {
      return $next($request);
    }
    // expires_at 만료되었는지 확인

    return response()->json(['message' => 'Unauthorized.'], 401);
  }
}
