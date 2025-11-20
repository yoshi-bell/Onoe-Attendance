<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;

use App\Services\CalendarService;

class StaffController extends Controller
{
    private $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * スタッフ一覧を表示する
     */
    public function index()
    {
        $staffs = User::where('is_admin', false)->orderBy('id', 'asc')->paginate(20);
        return view('admin.staff.list', compact('staffs'));
    }

    /**
     * 特定のスタッフの月別勤怠一覧を表示する
     */
    public function showAttendance(Request $request, User $user)
    {
        $today = Carbon::today();
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

        $calendarData = $this->calendarService->generate($user, $currentDate);

        return view('admin.staff.attendance_list', compact('calendarData', 'prevMonth', 'nextMonth', 'currentDate', 'today', 'user'));
    }

    /**
     * スタッフの月次勤怠一覧をCSVとしてエクスポートする
     */
    public function exportCsv(Request $request, User $user)
    {
        $month = $request->input('month', Carbon::now()->format('Y/m'));
        $currentDate = Carbon::createFromFormat('Y/m', $month)->startOfMonth();
        $calendarData = $this->calendarService->generate($user, $currentDate);

        $fileName = 'attendance_' . $user->name . '_' . $currentDate->format('Ym') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($calendarData) {
            $stream = fopen('php://output', 'w');
            stream_filter_prepend($stream, 'convert.iconv.utf-8/cp932//TRANSLIT');

            fputcsv($stream, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計',
            ]);

            foreach ($calendarData as $dayData) {
                if ($dayData['attendance']) {
                    fputcsv($stream, [
                        $dayData['date'],
                        Carbon::parse($dayData['attendance']->start_time)->format('H:i'),
                        $dayData['attendance']->end_time ? Carbon::parse($dayData['attendance']->end_time)->format('H:i') : '',
                        $dayData['attendance']->total_rest_time,
                        $dayData['attendance']->work_time ?? '',
                    ]);
                } else {
                    fputcsv($stream, [
                        $dayData['date'],
                        '', // 出勤
                        '', // 退勤
                        '', // 休憩
                        '', // 合計
                    ]);
                }
            }

            fclose($stream);
        };

        return Response::stream($callback, 200, $headers);
    }
}