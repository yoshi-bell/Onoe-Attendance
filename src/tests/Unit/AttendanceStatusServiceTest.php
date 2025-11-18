<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use App\Services\AttendanceStatusService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function ユーザーが指定されていない場合は勤務外になる()
    {
        $status = new AttendanceStatusService(null);

        $this->assertEquals('勤務外', $status->statusText);
        $this->assertFalse($status->isWorking);
        $this->assertFalse($status->isOnBreak);
        $this->assertFalse($status->hasFinishedWork);
    }

    /**
     * @test
     */
    public function 今日の勤怠記録がない場合は勤務外になる()
    {
        $user = User::factory()->create();
        $status = new AttendanceStatusService($user);

        $this->assertEquals('勤務外', $status->statusText);
        $this->assertFalse($status->isWorking);
    }

    /**
     * @test
     */
    public function 出勤打刻のみの場合は出勤中になる()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::now(),
            'end_time' => null,
        ]);

        $status = new AttendanceStatusService($user);

        $this->assertEquals('出勤中', $status->statusText);
        $this->assertTrue($status->isWorking);
        $this->assertFalse($status->isOnBreak);
        $this->assertFalse($status->hasFinishedWork);
    }

    /**
     * @test
     */
    public function 休憩開始打刻のみの場合は休憩中になる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::now()->subHour(),
            'end_time' => null,
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now(),
            'end_time' => null,
        ]);

        $status = new AttendanceStatusService($user);

        $this->assertEquals('休憩中', $status->statusText);
        $this->assertTrue($status->isWorking);
        $this->assertTrue($status->isOnBreak);
        $this->assertFalse($status->hasFinishedWork);
    }

    /**
     * @test
     */
    public function 退勤打刻済みの場合は退勤済になる()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->subHour(),
        ]);

        $status = new AttendanceStatusService($user);

        $this->assertEquals('退勤済', $status->statusText);
        $this->assertFalse($status->isWorking);
        $this->assertFalse($status->isOnBreak);
        $this->assertTrue($status->hasFinishedWork);
    }
}
