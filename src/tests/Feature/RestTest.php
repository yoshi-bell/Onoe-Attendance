<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class RestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group rest
     * 休憩ボタンが正しく機能する
     *
     * @return void
     */
    public function 休憩ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 10, 0, 0));
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(), // 固定された「今日」
            'start_time' => Carbon::now()->subHour(), // 09:00:00
            'end_time' => null,
        ]);

        $this->get('/attendance')->assertSee('休憩入');

        Carbon::setTestNow(Carbon::create(2025, 11, 3, 12, 0, 0));
        $response = $this->post('/rest/start');

        $this->assertDatabaseHas('rests', [
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now(),
        ]);

        $response->assertRedirect('/attendance');
        $this->get('/attendance')->assertSee('休憩中');
    }

    /**
     * @test
     * @group rest
     * 休憩は一日に何回でもできる
     *
     * @return void
     */
    public function 休憩は一日に何回でもできる()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 9, 0, 0));
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(), // 固定された「今日」
            'start_time' => Carbon::now(),
            'end_time' => null,
        ]);

        Carbon::setTestNow(Carbon::create(2025, 11, 3, 12, 0, 0));
        $this->post('/rest/start');
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 13, 0, 0));
        $this->post('/rest/end');

        $this->get('/attendance')->assertSee('休憩入');

        Carbon::setTestNow(Carbon::create(2025, 11, 3, 15, 0, 0));
        $this->post('/rest/start');

        $this->assertDatabaseCount('rests', 2);
    }

    /**
     * @test
     * @group rest
     * 休憩戻ボタンが正しく機能する
     *
     * @return void
     */
    public function 休憩戻ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 9, 0, 0));
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(), // 固定された「今日」
            'start_time' => Carbon::now(),
            'end_time' => null,
        ]);

        Carbon::setTestNow(Carbon::create(2025, 11, 3, 12, 0, 0));
        $rest = Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now(),
            'end_time' => null,
        ]);

        $this->get('/attendance')->assertSee('休憩戻');

        Carbon::setTestNow(Carbon::create(2025, 11, 3, 13, 0, 0));
        $response = $this->post('/rest/end');

        $this->assertDatabaseHas('rests', [
            'id' => $rest->id,
            'end_time' => Carbon::now(),
        ]);

        $response->assertRedirect('/attendance');
        $this->get('/attendance')->assertSee('出勤中');
    }

    /**
     * @test
     * @group rest
     * 休憩戻は一日に何回でもできる
     *
     * @return void
     */
    public function 休憩戻は一日に何回でもできる()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 9, 0, 0));
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(), // 固定された「今日」
            'start_time' => Carbon::now(),
            'end_time' => null,
        ]);

        Carbon::setTestNow(Carbon::create(2025, 11, 3, 12, 0, 0));
        $this->post('/rest/start');
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 13, 0, 0));
        $this->post('/rest/end');

        Carbon::setTestNow(Carbon::create(2025, 11, 3, 15, 0, 0));
        $this->post('/rest/start');

        $this->get('/attendance')->assertSee('休憩戻');

        Carbon::setTestNow(Carbon::create(2025, 11, 3, 16, 0, 0));
        $this->post('/rest/end');

        $this->assertDatabaseCount('rests', 2);
        $this->assertDatabaseMissing('rests', ['end_time' => null]);
    }

    /**
     * @test
     * @group rest
     * 休憩時刻が勤怠一覧画面で確認できる
     *
     * @return void
     */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::parse('12:00:00'),
            'end_time' => Carbon::parse('12:30:00'),
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::parse('15:00:00'),
            'end_time' => Carbon::parse('15:15:00'),
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('0:45');
    }
}
