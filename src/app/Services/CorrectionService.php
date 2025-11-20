<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Rest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CorrectionService
{
    /**
     * Store a new attendance correction request.
     *
     * @param Request $request
     * @param Attendance $attendance
     * @return void
     */
    public function storeRequest(Request $request, Attendance $attendance)
    {
        DB::transaction(function () use ($request, $attendance) {
            $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');

            $attendanceCorrection = $attendance->corrections()->create([
                'requester_id' => Auth::id(),
                'requested_start_time' => $workDate . ' ' . $request->input('requested_start_time'),
                'requested_end_time' => $workDate . ' ' . $request->input('requested_end_time'),
                'reason' => $request->input('reason'),
                'status' => 'pending', // Explicitly set status
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
    }

    /**
     * Approve an attendance correction request.
     *
     * @param AttendanceCorrection $attendanceCorrection
     * @return void
     */
    public function approveRequest(AttendanceCorrection $attendanceCorrection)
    {
        if ($attendanceCorrection->status !== 'pending') {
            return; // Or throw an exception
        }

        DB::transaction(function () use ($attendanceCorrection) {
            $attendance = $attendanceCorrection->attendance;
            $attendance->start_time = $attendanceCorrection->requested_start_time;
            $attendance->end_time = $attendanceCorrection->requested_end_time;
            $attendance->save();

            $attendance->rests()->delete(); // Delete all existing rests
            foreach ($attendanceCorrection->restCorrections as $restCorrection) {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $restCorrection->requested_start_time,
                    'end_time' => $restCorrection->requested_end_time,
                ]);
            }

            $attendanceCorrection->status = 'approved';
            $attendanceCorrection->save();
        });
    }
}
