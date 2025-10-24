<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrection;

use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $query = AttendanceCorrection::with('requester', 'attendance.user') // 申請者と勤怠ユーザー情報をEager Load
            ->orderBy('created_at', 'desc');

        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'approved') {
            $query->where('status', 'approved');
        }

        $corrections = $query->get();

        return view('admin.correction.list', compact('corrections', 'status'));
    }

    /**
     * 修正申請の詳細を表示する
     */
    public function show(AttendanceCorrection $attendanceCorrection)
    {
        $attendanceCorrection->load(['requester', 'attendance.user', 'attendance.rests', 'restCorrections']);

        return view('admin.correction.approve', compact('attendanceCorrection'));
    }

    /**
     * 修正申請を承認する
     */
    public function approve(Request $request, AttendanceCorrection $attendanceCorrection)
    {
        // 既に承認済みまたは却下済みの場合は何もしない
        if ($attendanceCorrection->status !== 'pending') {
            return redirect()->back()->with('error', 'この申請は既に処理されています。');
        }

        DB::transaction(function () use ($attendanceCorrection) {
            // 勤怠修正を適用
            $attendance = $attendanceCorrection->attendance;
            $attendance->start_time = $attendanceCorrection->requested_start_time;
            $attendance->end_time = $attendanceCorrection->requested_end_time;
            $attendance->save();

            // 既存の休憩を削除し、修正後の休憩を適用
            $attendance->rests()->delete(); // 既存の休憩を全て削除
            foreach ($attendanceCorrection->restCorrections as $restCorrection) {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $restCorrection->requested_start_time,
                    'end_time' => $restCorrection->requested_end_time,
                ]);
            }

            // 申請ステータスを承認済みに更新
            $attendanceCorrection->status = 'approved';
            // approver_id は削除済みのため、この行は不要
            $attendanceCorrection->save();
        });

        return redirect()->route('admin.corrections.approve.show', ['attendanceCorrection' => $attendanceCorrection->id])->with('success', '申請を承認しました。');
    }
}
