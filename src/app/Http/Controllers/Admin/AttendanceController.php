<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Http\Controllers\Controller;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $attendances = Attendance::whereDate('work_date', $date)
            ->with(['user', 'rests'])
            ->paginate(20);

        $prevDate = $date->copy()->subDay()->format('Y-m-d');
        $nextDate = $date->copy()->addDay()->format('Y-m-d');
        $today = Carbon::today();

        return view('admin.attendance.index', compact('attendances', 'date', 'prevDate', 'nextDate', 'today'));
    }

    /**
     * 勤怠詳細画面を表示する
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['user', 'rests', 'corrections.restCorrections']);
        $pendingCorrection = $attendance->corrections->where('status', 'pending')->last();

        return view('admin.attendance.detail', compact('attendance', 'pendingCorrection'));
    }

    /**
     * 勤怠情報を更新する
     */
    public function update(AttendanceCorrectionRequest $request, Attendance $attendance)
    {
        $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');
        $attendance->start_time = $workDate . ' ' . $request->input('requested_start_time');
        $attendance->end_time = $workDate . ' ' . $request->input('requested_end_time');
        $attendance->save();

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

        return redirect()->route('admin.attendance.show', ['attendance' => $attendance->id])->with('success', '勤怠情報を更新しました');
    }
}
