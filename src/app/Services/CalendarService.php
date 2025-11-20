<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class CalendarService
{
    /**
     * Generate monthly attendance calendar data for a user.
     *
     * @param User $user
     * @param Carbon $currentDate
     * @return array
     */
    public function generate(User $user, Carbon $currentDate): array
    {
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $currentDate->year)
            ->whereMonth('work_date', $currentDate->month)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->day;
            });

        $daysInMonth = $currentDate->daysInMonth;
        $calendarData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentDate->copy()->day($day);
            $attendanceForDay = $attendances->get($day);

            $formattedDate = Attendance::getFormattedDateWithDay(
                $attendanceForDay ? $attendanceForDay->work_date : $date,
                'm/d'
            );

            $calendarData[] = [
                'date' => $formattedDate,
                'attendance' => $attendanceForDay
            ];
        }

        return $calendarData;
    }
}
