<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧を表示する
     */
    public function index()
    {
        $staffs = User::where('is_admin', false)->orderBy('name')->get();

        return view('admin.staff.list', compact('staffs'));
    }

    /**
     * 特定のスタッフの月別勤怠一覧を表示する
     */
    public function showAttendance(Request $request, User $user)
    {
        $today = Carbon::today();
        // クエリ文字列から月を取得、なければ現在の月を使う
        $month = $request->input('month', Carbon::now()->format('Y/m'));
        $currentDate = Carbon::createFromFormat('Y/m', $month)->startOfMonth();
        $prevMonth = $currentDate->copy()->subMonth()->format('Y/m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y/m');

        // 対象月の勤怠記録をすべて取得し、日付をキーにした連想配列に変換
        $attendances = Attendance::where('user_id', $user->id) // 認証ユーザーではなく、指定されたユーザーのIDを使用
            ->whereYear('work_date', $currentDate->year)
            ->whereMonth('work_date', $currentDate->month)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->day;
            });

        // カレンダーデータを作成
        $daysInMonth = $currentDate->daysInMonth;
        $calendarData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentDate->copy()->day($day);
            // その日の勤怠データが存在するかチェック
            $attendanceForDay = $attendances->get($day);

            // 曜日を取得
            $week = ['日', '月', '火', '水', '木', '金', '土'];
            $dayOfWeek = $week[$date->dayOfWeek];

            // ビューに渡すための配列に追加
            $calendarData[] = [
                'date' => $date->format('m/d') . '(' . $dayOfWeek . ')',
                'attendance' => $attendanceForDay // データがなければnullが入る
            ];
        }

        return view('admin.staff.attendance_list', compact('calendarData', 'prevMonth', 'nextMonth', 'currentDate', 'today', 'user'));
    }
}
