<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class WorkStartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group work-start
     * 出勤ボタンが正しく機能する
     *
     * @return void
     */
    public function 出勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 9, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/attendance/start');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now(),
        ]);

        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * @test
     * @group work-start
     * 出勤は一日一回のみできる (UI)
     *
     * @return void
     */
    public function 出勤は一日一回のみできる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $today = Carbon::today();
        $startTime = $today->copy()->setHour(9);
        $endTime = $today->copy()->setHour(18);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $response = $this->get('/attendance');

        $response->assertDontSee('<button type="submit" class="timestamp-button">出勤</button>', false);
    }

    /**
     * @test
     * @group work-start
     * 出勤は一日一回のみできる (APIレベル)
     *
     * @return void
     */
    public function 出勤は一日一回のみできる_APIレベル()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow(Carbon::create(2025, 11, 3, 9, 0, 0));
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::now(),
            'end_time' => null,
        ]);

        Carbon::setTestNow(Carbon::create(2025, 11, 3, 9, 5, 0));
        $response = $this->post('/attendance/start');

        $this->assertDatabaseCount('attendances', 1);
        $response->assertRedirect('/attendance');
    }

    /**
     * @test
     * @group work-start
     * 出勤時刻が勤怠一覧画面で確認できる
     *
     * @return void
     */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $startTime = Carbon::create(2025, 11, 3, 9, 30, 0);
        Carbon::setTestNow($startTime);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $startTime->today(),
            'start_time' => $startTime,
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($startTime->format('H:i'));
    }
}