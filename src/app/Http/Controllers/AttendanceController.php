<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AttendanceCorrectionRequest;

use App\ViewModels\AttendanceIndexData;
use App\Services\CalendarService;
use App\Services\CorrectionService;

class AttendanceController extends Controller
{
    private $calendarService;
    private $correctionService;

    public function __construct(
        CalendarService $calendarService,
        CorrectionService $correctionService
    ) {
        $this->calendarService = $calendarService;
        $this->correctionService = $correctionService;
    }

    /**
     * Display the attendance clock-in/out page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('attendance.index', [
            'attendanceData' => new AttendanceIndexData(Auth::user())
        ]);
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

        $calendarData = $this->calendarService->generate(Auth::user(), $currentDate);

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
            $this->correctionService->storeRequest($request, $attendance);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '申請の送信に失敗しました。もう一度お試しください。');
        }

        return redirect()->back();
    }
}
