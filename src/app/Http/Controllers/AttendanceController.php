<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AttendanceCorrectionRequest;

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

        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (!$existingAttendance) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $today,
                'start_time' => Carbon::now(),
            ]);
        }

        return redirect()->route('attendance');
    }

    /**
     * 勤務を終了する
     */
    public function endWork()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->whereNull('end_time')
            ->first();

        if ($attendance) {
            $latestBreak = $attendance->rests()->latest()->first();
            $isOnBreak = $latestBreak && !$latestBreak->end_time;

            if (!$isOnBreak) {
                $attendance->update([
                    'end_time' => Carbon::now(),
                ]);
                return redirect()->route('attendance');
            }
        }

        return redirect()->route('attendance');
    }

    /**
     * 月別の勤怠一覧を表示する
     */
    public function list(Request $request)
    {
        $today = Carbon::today();
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', Auth::id())
            ->whereYear('work_date', $currentDate->year)
            ->whereMonth('work_date', $currentDate->month)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->day;
            });

        $daysInMonth = $currentDate->daysInMonth;
        $calendarData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentDate->copy()->day($day);
            $attendanceForDay = $attendances->get($day);

            $week = ['日', '月', '火', '水', '木', '金', '土'];
            $dayOfWeek = $week[$date->dayOfWeek];

            $calendarData[] = [
                'date' => $date->format('m/d') . '(' . $dayOfWeek . ')',
                'attendance' => $attendanceForDay
            ];
        }

        return view('attendance.list', compact('calendarData', 'prevMonth', 'nextMonth', 'currentDate', 'today'));
    }

    /**
     * 特定の日の勤怠詳細を表示する
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['rests', 'corrections.restCorrections']);
        $pendingCorrection = $attendance->corrections->where('status', 'pending')->last();

        return view('attendance.detail', compact('attendance', 'pendingCorrection'));
    }

    /**
     * 勤怠修正申請を保存する
     */
    public function storeCorrection(AttendanceCorrectionRequest $request, Attendance $attendance)
    {
        try {
            DB::transaction(function () use ($request, $attendance) {
                $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');

                $attendanceCorrection = $attendance->corrections()->create([
                    'requester_id' => Auth::id(),
                    'requested_start_time' => $workDate . ' ' . $request->input('requested_start_time'),
                    'requested_end_time' => $workDate . ' ' . $request->input('requested_end_time'),
                    'reason' => $request->input('reason'),
                ]);

                if ($request->has('rests')) {
                    foreach ($request->input('rests') as $restData) {
                        if (!empty($restData['start_time']) && !empty($restData['end_time'])) {
                            $attendanceCorrection->restCorrections()->create([
                                'requested_start_time' => $workDate . ' ' . $restData['start_time'],
                                'requested_end_time' => $workDate . ' ' . $restData['end_time'],
                            ]);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '申請の送信に失敗しました。もう一度お試しください。');
        }

        return redirect()->back();
    }
}
