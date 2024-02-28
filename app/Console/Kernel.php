<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        $schedule->call(function() {
            // 로그: 작업 시작
            Log::info('Scheduled task started: Deleting expired tokens.');

            // 현재 시간
            $now = Carbon::now();
            // 로그: 현재 시간 기록
            Log::info('Current time: ' . $now->toDateTimeString());
            // DB에서 만료된 ACCESS TOKEN과 Refresh Token을 찾아서 삭제
            DB::table('personal_access_tokens')->where('expires_at', '<', $now)->delete();
            // 로그: 작업 종료

            Log::info('Scheduled task completed: Deleted expired tokens.');
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
