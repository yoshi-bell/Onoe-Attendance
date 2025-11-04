<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group attendance-list
     * 自分が行った勤怠情報が全て表示されている
     *
     * @return void
     */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 15, 10, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        // 自分の勤怠データを2件作成
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->startOfMonth(),
            'start_time' => Carbon::today()->startOfMonth()->setHour(9),
            'end_time' => Carbon::today()->startOfMonth()->setHour(18),
        ]);
        Rest::factory()->create(['attendance_id' => $attendance1->id]);
        $attendance1->refresh();

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->startOfMonth()->addDay(),
            'start_time' => Carbon::today()->startOfMonth()->addDay()->setHour(9),
            'end_time' => Carbon::today()->startOfMonth()->addDay()->setHour(18),
        ]);
        Rest::factory()->create(['attendance_id' => $attendance2->id]);
        $attendance2->refresh();

        // 他のユーザーの勤怠データを作成
        $otherUser = User::factory()->create();
        $otherAttendance = Attendance::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($attendance1->start_time->format('H:i'));
        $response->assertSee($attendance1->end_time->format('H:i'));
        $response->assertSee($attendance1->total_rest_time);
        $response->assertSee($attendance1->work_time);

        $response->assertSee($attendance2->start_time->format('H:i'));
        $response->assertSee($attendance2->end_time->format('H:i'));
        $response->assertSee($attendance2->total_rest_time);
        $response->assertSee($attendance2->work_time);

        $response->assertDontSee($otherAttendance->start_time->format('H:i'));
    }

    /**
     * @test
     * @group attendance-list
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     *
     * @return void
     */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 15, 10, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('value="2025/11"', false);
    }

    /**
     * @test
     * @group attendance-list
     * 「前月」を押下した時に表示月の前月の情報が表示される
     *
     * @return void
     */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 15, 10, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        $prevMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->subMonth()->startOfMonth(),
            'start_time' => Carbon::today()->subMonth()->startOfMonth()->setHour(9),
            'end_time' => Carbon::today()->subMonth()->startOfMonth()->setHour(18),
        ]);
        Rest::factory()->create(['attendance_id' => $prevMonthAttendance->id]);
        $prevMonthAttendance->refresh();

        $prevMonth = Carbon::today()->subMonth()->format('Y/m');
        $response = $this->get('/attendance/list?month=' . $prevMonth);

        $response->assertStatus(200);
        $response->assertSee($prevMonthAttendance->start_time->format('H:i'));
        $response->assertSee($prevMonthAttendance->end_time->format('H:i'));
        $response->assertSee($prevMonthAttendance->total_rest_time);
        $response->assertSee($prevMonthAttendance->work_time);
        $response->assertSee('value="' . $prevMonth . '"', false);
    }

    /**
     * @test
     * @group attendance-list
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     *
     * @return void
     */
    public function 「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 15, 10, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        $nextMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->addMonth()->startOfMonth(),
            'start_time' => Carbon::today()->addMonth()->startOfMonth()->setHour(9),
            'end_time' => Carbon::today()->addMonth()->startOfMonth()->setHour(18),
        ]);
        Rest::factory()->create(['attendance_id' => $nextMonthAttendance->id]);
        $nextMonthAttendance->refresh();

        $nextMonth = Carbon::today()->addMonth()->format('Y/m');
        $response = $this->get('/attendance/list?month=' . $nextMonth);

        $response->assertStatus(200);
        $response->assertSee($nextMonthAttendance->start_time->format('H:i'));
        $response->assertSee($nextMonthAttendance->end_time->format('H:i'));
        $response->assertSee($nextMonthAttendance->total_rest_time);
        $response->assertSee($nextMonthAttendance->work_time);
        $response->assertSee('value="' . $nextMonth . '"', false);
    }

    /**
     * @test
     * @group attendance-list
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     *
     * @return void
     */
    public function 「詳細」を押下すると_その日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 15, 10, 0, 0));
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->subDay(),
        ]);

        $response = $this->get(route('attendance.detail', ['attendance' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($user->name);
        $response->assertSee(Carbon::parse($attendance->work_date)->format('Y年'));
        $response->assertSee(Carbon::parse($attendance->work_date)->format('m月d日'));
    }
}