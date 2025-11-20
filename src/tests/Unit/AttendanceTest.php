<?php

namespace Tests\Unit;

use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    /**
     * @test
     * @group AttendanceStaticMethod
     */
    public function getFormattedDateWithDayが日付を正しくフォーマットする()
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

        foreach ($testDates as $dateString => $expectedFormattedDate) {
            $date = Carbon::parse($dateString);
            $formattedDate = Attendance::getFormattedDateWithDay($date);
            $this->assertEquals($expectedFormattedDate, $formattedDate, "Failed for date: {$dateString}");
        }
    }
}
