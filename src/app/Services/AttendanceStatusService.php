<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceStatusService
{
    public string $statusText = '勤務外';
    public bool $isWorking = false;
    public bool $isOnBreak = false;
    public bool $hasFinishedWork = false;

    public function __construct(?User $user)
    {
        if (!$user) {
            return;
        }

        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if ($attendance) {
            if ($attendance->end_time) {
                $this->hasFinishedWork = true;
                $this->statusText = '退勤済';
            } elseif ($attendance->start_time) {
                $this->isWorking = true;
                $this->statusText = '出勤中';
                $latestBreak = $attendance->rests()->latest()->first();
                if ($latestBreak && !$latestBreak->end_time) {
                    $this->isOnBreak = true;
                    $this->statusText = '休憩中';
                }
            }
        }
    }
}
