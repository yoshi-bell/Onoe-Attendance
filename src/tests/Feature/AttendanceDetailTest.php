<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group attendance-detail
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     *
     * @return void
     */
    public function 勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('attendance.detail', ['attendance' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    /**
     * @test
     * @group attendance-detail
     * 勤怠詳細画面の「日付」が選択した日付になっている
     *
     * @return void
     */
    public function 勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $workDate = Carbon::create(2025, 11, 10);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $workDate,
        ]);

        $response = $this->get(route('attendance.detail', ['attendance' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($workDate->format('Y年'));
        $response->assertSee($workDate->format('m月d日'));
    }

    /**
     * @test
     * @group attendance-detail
     * 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     *
     * @return void
     */
    public function 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $startTime = Carbon::create(2025, 11, 10, 9, 0, 0);
        $endTime = Carbon::create(2025, 11, 10, 18, 0, 0);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $startTime->today(),
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $response = $this->get(route('attendance.detail', ['attendance' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('value="' . $startTime->format('H:i') . '"', false);
        $response->assertSee('value="' . $endTime->format('H:i') . '"', false);
    }

    /**
     * @test
     * @group attendance-detail
     * 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     *
     * @return void
     */
    public function 「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $restStartTime = Carbon::create(2025, 11, 10, 12, 0, 0);
        $restEndTime = Carbon::create(2025, 11, 10, 13, 0, 0);

        $rest = Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => $restStartTime,
            'end_time' => $restEndTime,
        ]);

        $response = $this->get(route('attendance.detail', ['attendance' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('value="' . $restStartTime->format('H:i') . '"', false);
        $response->assertSee('value="' . $restEndTime->format('H:i') . '"', false);
    }
}
