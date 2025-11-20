<?php

namespace App\ViewModels;

use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceStatusService;
use Carbon\Carbon;

class AttendanceIndexData
{
    public string $date;
    public string $time;
    public AttendanceStatusService $status;

    public function __construct(?User $user)
    {
        $now = Carbon::now();
        $this->date = Attendance::getFormattedDateWithDay($now);
        $this->time = $now->format('H:i');
        $this->status = new AttendanceStatusService($user);
    }
}
