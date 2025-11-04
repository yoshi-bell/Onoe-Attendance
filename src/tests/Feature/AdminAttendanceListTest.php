<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group admin-attendance-list
     * その日になされた全ユーザーの勤怠情報が正確に確認できる
     *
     * @return void
     */
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 10, 0, 0));

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::today()->setHour(9),
            'end_time' => Carbon::today()->setHour(18),
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance1->id,
            'start_time' => Carbon::today()->setHour(12),
            'end_time' => Carbon::today()->setHour(13),
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::today()->setHour(9)->addMinutes(10),
            'end_time' => Carbon::today()->setHour(18)->addMinutes(10),
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance2->id,
            'start_time' => Carbon::today()->setHour(12)->addMinutes(10),
            'end_time' => Carbon::today()->setHour(13)->addMinutes(10),
        ]);

        // モデルをリフレッシュしてアクセサを再計算させる
        $attendance1->refresh();
        $attendance2->refresh();

        $response = $this->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee($attendance1->start_time->format('H:i'));
        $response->assertSee($attendance1->end_time->format('H:i'));
        $response->assertSee($attendance1->total_rest_time);
        $response->assertSee($attendance1->work_time);
        $response->assertSee($attendance2->start_time->format('H:i'));
        $response->assertSee($attendance2->end_time->format('H:i'));
        $response->assertSee($attendance2->total_rest_time);
        $response->assertSee($attendance2->work_time);
    }

    /**
     * @test
     * @group admin-attendance-list
     * 遷移した際に現在の日付が表示される
     *
     * @return void
     */
    public function 遷移した際に現在の日付が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 10, 0, 0));

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee(Carbon::today()->format('Y年n月j日'));
    }

    /**
     * @test
     * @group admin-attendance-list
     * 「前日」を押下した時に前の日の勤怠情報が表示される
     *
     * @return void
     */
    public function 「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 10, 0, 0));

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $prevDayAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::yesterday(),
            'start_time' => Carbon::yesterday()->setHour(9),
            'end_time' => Carbon::yesterday()->setHour(18),
        ]);
        Rest::factory()->create([
            'attendance_id' => $prevDayAttendance->id,
            'start_time' => Carbon::yesterday()->setHour(12),
            'end_time' => Carbon::yesterday()->setHour(13),
        ]);
        $prevDayAttendance->refresh();

        $prevDate = Carbon::yesterday()->format('Y-m-d');
        $response = $this->get(route('admin.attendance.index', ['date' => $prevDate]));

        $response->assertStatus(200);
        $response->assertSee(Carbon::yesterday()->format('Y年n月j日'));
        $response->assertSee($user->name);
        $response->assertSee($prevDayAttendance->start_time->format('H:i'));
        $response->assertSee($prevDayAttendance->end_time->format('H:i'));
        $response->assertSee($prevDayAttendance->total_rest_time);
        $response->assertSee($prevDayAttendance->work_time);
    }

    /**
     * @test
     * @group admin-attendance-list
     * 「翌日」を押下した時に次の日の勤怠情報が表示される
     *
     * @return void
     */
    public function 「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 10, 0, 0));

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $nextDayAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::tomorrow(),
            'start_time' => Carbon::tomorrow()->setHour(9),
            'end_time' => Carbon::tomorrow()->setHour(18),
        ]);
        Rest::factory()->create([
            'attendance_id' => $nextDayAttendance->id,
            'start_time' => Carbon::tomorrow()->setHour(12),
            'end_time' => Carbon::tomorrow()->setHour(13),
        ]);
        $nextDayAttendance->refresh();

        $nextDate = Carbon::tomorrow()->format('Y-m-d');
        $response = $this->get(route('admin.attendance.index', ['date' => $nextDate]));

        $response->assertStatus(200);
        $response->assertSee(Carbon::tomorrow()->format('Y年n月j日'));
        $response->assertSee($user->name);
        $response->assertSee($nextDayAttendance->start_time->format('H:i'));
        $response->assertSee($nextDayAttendance->end_time->format('H:i'));
        $response->assertSee($nextDayAttendance->total_rest_time);
        $response->assertSee($nextDayAttendance->work_time);
    }
}
