<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class RestController extends Controller
{
    /**
     * 休憩を開始する
     */
    public function start()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 今日の、まだ終了していない勤怠記録を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->whereNull('end_time')
            ->first();

        // 勤怠記録があり、かつ現在休憩中でないことを確認
        if ($attendance) {
            $latestBreak = $attendance->rests()->latest()->first();
            $isOnBreak = $latestBreak && !$latestBreak->end_time;

            if (!$isOnBreak) {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => Carbon::now(),
                ]);
            }
        }

        return redirect()->route('attendance');
    }

    /**
     * 休憩を終了する
     */
    public function end()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 今日の、まだ終了していない勤怠記録を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->whereNull('end_time')
            ->first();

        if ($attendance) {
            // 休憩開始していて、まだ終了していない最新の休憩記録を取得
            $break = $attendance->rests()
                ->whereNull('end_time')
                ->latest()
                ->first();

            if ($break) {
                $break->update([
                    'end_time' => Carbon::now(),
                ]);
            }
        }

        return redirect()->route('attendance');
    }
}
