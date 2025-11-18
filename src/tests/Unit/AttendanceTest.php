<?php

namespace Tests\Unit;

use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    /**
     * @test
     * @group AttendanceAccessor
     */
    public function work_dateが日本語の曜日付きでフォーマットされる()
    {
        $testDates = [
            '2023-11-19' => '2023年11月19日(日)', // Sunday
            '2023-11-20' => '2023年11月20日(月)', // Monday
            '2023-11-21' => '2023年11月21日(火)', // Tuesday
            '2023-11-22' => '2023年11月22日(水)', // Wednesday
            '2023-11-23' => '2023年11月23日(木)', // Thursday
            '2023-11-24' => '2023年11月24日(金)', // Friday
            '2023-11-25' => '2023年11月25日(土)', // Saturday
        ];

        $attendance = new Attendance();

        foreach ($testDates as $dateString => $expectedFormattedDate) {
            $attendance->work_date = $dateString;
            $formattedDate = $attendance->formatted_work_date;
            $this->assertEquals($expectedFormattedDate, $formattedDate, "Failed for date: {$dateString}");
        }
    }
}
