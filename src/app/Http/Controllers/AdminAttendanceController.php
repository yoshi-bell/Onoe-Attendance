<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\AttendanceCorrectionRequest;

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
        $today = Carbon::today(); // $today変数を定義

        return view('admin.attendance.index', compact('attendances', 'date', 'prevDate', 'nextDate', 'today'));
    }

    /**
     * 勤怠詳細画面を表示する
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['user', 'rests', 'corrections.restCorrections']);
        // 承認待ちの申請の中から最新のものを取得
        $pendingCorrection = $attendance->corrections->where('status', 'pending')->last();

        return view('admin.attendance.detail', compact('attendance', 'pendingCorrection'));
    }

    /**
     * 勤怠情報を更新する
     */
    public function update(AttendanceCorrectionRequest $request, Attendance $attendance)
    {
        // 勤怠修正を適用
        $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');
        $attendance->start_time = $workDate . ' ' . $request->input('requested_start_time');
        $attendance->end_time = $workDate . ' ' . $request->input('requested_end_time');
        $attendance->save();

        // 既存の休憩を削除し、修正後の休憩を適用
        $attendance->rests()->delete();
        if ($request->has('rests')) {
            foreach ($request->input('rests') as $restData) {
                if (!empty($restData['start_time']) && !empty($restData['end_time'])) {
                    $attendance->rests()->create([
                        'start_time' => $workDate . ' ' . $restData['start_time'],
                        'end_time' => $workDate . ' ' . $restData['end_time'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.attendance.show', ['attendance' => $attendance->id])->with('success', '勤怠情報を更新しました。');
    }
}
