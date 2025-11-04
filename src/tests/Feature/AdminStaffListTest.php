<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group admin-staff-list
     * 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     *
     * @return void
     */
    public function 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user1->email);
        $response->assertSee($user2->name);
        $response->assertSee($user2->email);
    }

    /**
     * @test
     * @group admin-staff-list
     * ユーザーの勤怠情報が正しく表示される
     *
     * @return void
     */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 15, 10, 0, 0));

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->startOfMonth(),
            'start_time' => Carbon::today()->startOfMonth()->setHour(9),
            'end_time' => Carbon::today()->startOfMonth()->setHour(18),
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::today()->startOfMonth()->setHour(12),
            'end_time' => Carbon::today()->startOfMonth()->setHour(13),
        ]);
        $attendance->refresh(); // アクセサを再計算させる

        $response = $this->get(route('admin.attendance.staff.showAttendance', ['user' => $user->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->start_time->format('H:i'));
        $response->assertSee($attendance->end_time->format('H:i'));
        $response->assertSee($attendance->total_rest_time);
        $response->assertSee($attendance->work_time);
    }

    /**
     *
     * @test
     * @group admin-staff-list
     * 「前月」を押下した時に表示月の前月の情報が表示される
     *
     * @return void
     */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 15, 10, 0, 0));

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $prevMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->subMonth()->startOfMonth(),
            'start_time' => Carbon::today()->subMonth()->startOfMonth()->setHour(9),
            'end_time' => Carbon::today()->subMonth()->startOfMonth()->setHour(18),
        ]);
        Rest::factory()->create([
            'attendance_id' => $prevMonthAttendance->id,
            'start_time' => Carbon::today()->subMonth()->startOfMonth()->setHour(12),
            'end_time' => Carbon::today()->subMonth()->startOfMonth()->setHour(13),
        ]);
        $prevMonthAttendance->refresh();

        $prevMonth = Carbon::today()->subMonth()->format('Y/m');
        $response = $this->get(route('admin.attendance.staff.showAttendance', ['user' => $user->id, 'month' => $prevMonth]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($prevMonthAttendance->start_time->format('H:i'));
        $response->assertSee($prevMonthAttendance->end_time->format('H:i'));
        $response->assertSee($prevMonthAttendance->total_rest_time);
        $response->assertSee($prevMonthAttendance->work_time);
        $response->assertSee('value="' . $prevMonth . '"', false);
    }

    /**
     * @test
     * @group admin-staff-list
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     *
     * @return void
     */
    public function 「翌月」を押下した時に次の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 15, 10, 0, 0));

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $nextMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->addMonth()->startOfMonth(),
            'start_time' => Carbon::today()->addMonth()->startOfMonth()->setHour(9),
            'end_time' => Carbon::today()->addMonth()->startOfMonth()->setHour(18),
        ]);
        Rest::factory()->create([
            'attendance_id' => $nextMonthAttendance->id,
            'start_time' => Carbon::today()->addMonth()->startOfMonth()->setHour(12),
            'end_time' => Carbon::today()->addMonth()->startOfMonth()->setHour(13),
        ]);
        $nextMonthAttendance->refresh();

        $nextMonth = Carbon::today()->addMonth()->format('Y/m');
        $response = $this->get(route('admin.attendance.staff.showAttendance', ['user' => $user->id, 'month' => $nextMonth]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($nextMonthAttendance->start_time->format('H:i'));
        $response->assertSee($nextMonthAttendance->end_time->format('H:i'));
        $response->assertSee($nextMonthAttendance->total_rest_time);
        $response->assertSee($nextMonthAttendance->work_time);
        $response->assertSee('value="' . $nextMonth . '"', false);
    }

    /**
     * @test
     * @group admin-staff-list
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     *
     * @return void
     */
    public function 「詳細」を押下すると_その日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 15, 10, 0, 0));

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->subDay(),
        ]);

        // スタッフ別勤怠一覧ページを開き、詳細リンクが存在することを確認
        $listResponse = $this->get(route('admin.attendance.staff.showAttendance', ['user' => $user->id]));
        $listResponse->assertStatus(200);
        $listResponse->assertSee(route('admin.attendance.show', ['attendance' => $attendance->id]));

        // 詳細リンクを直接叩いて詳細画面に遷移することを検証
        $detailResponse = $this->get(route('admin.attendance.show', ['attendance' => $attendance->id]));

        $detailResponse->assertStatus(200);
        $detailResponse->assertViewIs('admin.attendance.detail');
        $detailResponse->assertSee($user->name);
        $detailResponse->assertSee($attendance->work_date->format('Y年'));
        $detailResponse->assertSee($attendance->work_date->format('m月j日'));
    }
}
