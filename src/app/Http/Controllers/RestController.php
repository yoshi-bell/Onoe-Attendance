<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use App\Services\AttendanceStatusService;

class RestController extends Controller
{
    /**
     * 休憩を開始する
     */
    public function start()
    {
        $user = Auth::user();
        $status = new AttendanceStatusService($user);

        if ($status->isWorking && !$status->isOnBreak) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('work_date', Carbon::today())
                ->first();

            Rest::create([
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::now(),
            ]);
        }

        return redirect()->route('attendance');
    }

    /**
     * 休憩を終了する
     */
    public function end()
    {
        $user = Auth::user();
        $status = new AttendanceStatusService($user);

        if ($status->isOnBreak) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('work_date', Carbon::today())
                ->first();

            $break = $attendance->rests()
                ->whereNull('end_time')
                ->latest()
                ->first();

            if ($break) {
                $break->update([
                    'end_time' => Carbon::now(),
                ]);
            }
        }

        return redirect()->route('attendance');
    }
}
