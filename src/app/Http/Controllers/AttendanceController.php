<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display the attendance clock-in/out page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // ↓↓ 日時フォーマット処理を追加 ↓↓
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $date = $now->format('Y年m月d日') . '(' . $week[$now->dayOfWeek] . ')';
        $time = $now->format('H:i');

        $today = $now->copy()->startOfDay();
        $statusText = '勤務外';
        $isWorking = false;
        $isOnBreak = false;
        $hasFinishedWork = false;

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', $today)
            ->first();

        if ($attendance) {
            if ($attendance->end_time) {
                $hasFinishedWork = true;
                $statusText = '退勤済';
            } elseif ($attendance->start_time) {
                $isWorking = true;
                $statusText = '出勤中';
                $latestBreak = $attendance->rests()->latest()->first();
                if ($latestBreak && !$latestBreak->end_time) {
                    $isOnBreak = true;
                    $statusText = '休憩中';
                }
            }
        }

        // ↓↓ compactに $date と $time を追加 ↓↓
        return view('attendance.index', compact(
            'date',
            'time',
            'statusText',
            'isWorking',
            'isOnBreak',
            'hasFinishedWork'
        ));
    }

    /**
     * 勤務を開始する
     */
    public function startWork()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // すでに出勤記録があるか確認
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        // まだ出勤記録がなければ、新しい勤怠記録を作成
        if (!$existingAttendance) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $today,
                'start_time' => Carbon::now(),
            ]);
        }

        // 勤怠打刻ページにリダイレクト
        return redirect()->route('attendance');
    }

    /**
     * 勤務を終了する
     */
    public function endWork()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 今日の、まだ終了していない勤怠記録を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->whereNull('end_time')
            ->first();

        // 勤怠記録があり、かつ休憩中でないことを確認
        if ($attendance) {
            $latestBreak = $attendance->rests()->latest()->first();
            $isOnBreak = $latestBreak && !$latestBreak->end_time;

            if (!$isOnBreak) {
                $attendance->update([
                    'end_time' => Carbon::now(),
                ]);
                // 完了メッセージを付けてリダイレクト
                return redirect()->route('attendance');
            }
        }

        // 条件に合わない場合（休憩中など）は、メッセージなしでリダイレクト
        return redirect()->route('attendance');
    }
}
