<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 日付の取得（クエリがなければ今日）
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        // 指定された日付の勤怠データを取得
        $attendances = Attendance::whereDate('work_date', $date)
            ->with(['user', 'rests'])
            ->get();

        // 日付ナビゲーション用
        $prevDate = $date->copy()->subDay()->format('Y-m-d');
        $nextDate = $date->copy()->addDay()->format('Y-m-d');

        return view('admin.attendance.index', compact('attendances', 'date', 'prevDate', 'nextDate'));
    }
}
