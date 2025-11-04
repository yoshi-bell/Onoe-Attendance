<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class StatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group status
     * 勤務外の場合、勤怠ステータスが正しく表示される
     *
     * @return void
     */
    public function 勤務外の場合_勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /**
     * @test
     * @group status
     * 出勤中の場合、勤怠ステータスが正しく表示される
     *
     * @return void
     */
    public function 出勤中の場合_勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 12, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::today()->setHour(9),
            'end_time' => null,
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::today()->setHour(10),
            'end_time' => Carbon::today()->setHour(11),
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * @test
     * @group status
     * 休憩中の場合、勤怠ステータスが正しく表示される
     *
     * @return void
     */
    public function 休憩中の場合_勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 12, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::today()->setHour(9),
            'end_time' => null,
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::today()->setHour(11),
            'end_time' => null,
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * @test
     * @group status
     * 退勤済の場合、勤怠ステータスが正しく表示される
     *
     * @return void
     */
    public function 退勤済の場合_勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 19, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::today()->setHour(9),
            'end_time' => Carbon::today()->setHour(18),
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}