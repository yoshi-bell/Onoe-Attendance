<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class AdminCorrectionApprovalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group admin-correction-approval
     * 承認待ちの修正申請が全て表示されている
     *
     * @return void
     */
    public function 承認待ちの修正申請が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 10, 0, 0));

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = Attendance::factory()->create(['user_id' => $user1->id, 'work_date' => Carbon::today()->subDays(3)]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user1->id, 'work_date' => Carbon::today()->subDays(2)]);
        $attendance3 = Attendance::factory()->create(['user_id' => $user2->id, 'work_date' => Carbon::today()->subDays(1)]);
        $attendance4 = Attendance::factory()->create(['user_id' => $user1->id, 'work_date' => Carbon::today()]);

        // 表示されるべき申請 (承認待ち)
        $pendingCorrection1 = AttendanceCorrection::factory()->create(['attendance_id' => $attendance1->id, 'requester_id' => $user1->id, 'status' => 'pending', 'reason' => 'ユーザー1の保留中申請1', 'created_at' => Carbon::now()->subHours(5)]);
        $pendingCorrection2 = AttendanceCorrection::factory()->create(['attendance_id' => $attendance2->id, 'requester_id' => $user1->id, 'status' => 'pending', 'reason' => 'ユーザー1の保留中申請2', 'created_at' => Carbon::now()->subHours(4)]);
        $pendingCorrection3 = AttendanceCorrection::factory()->create(['attendance_id' => $attendance3->id, 'requester_id' => $user2->id, 'status' => 'pending', 'reason' => 'ユーザー2の保留中申請1', 'created_at' => Carbon::now()->subHours(3)]);

        // 表示されないべき申請 (承認済み)
        $approvedCorrection = AttendanceCorrection::factory()->create(['attendance_id' => $attendance4->id, 'requester_id' => $user1->id, 'status' => 'approved', 'reason' => '承認済み申請', 'created_at' => Carbon::now()->subHours(2)]);

        $response = $this->get(route('admin.corrections.index', ['status' => 'pending']));

        $response->assertStatus(200);
        // 申請理由
        $response->assertSee($pendingCorrection1->reason);
        $response->assertSee($pendingCorrection2->reason);
        $response->assertSee($pendingCorrection3->reason);
        $response->assertDontSee($approvedCorrection->reason);

        // 申請者名
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);

        // 対象勤怠年月日
        $response->assertSee($pendingCorrection1->attendance->work_date->format('Y/m/d'));
        $response->assertSee($pendingCorrection2->attendance->work_date->format('Y/m/d'));
        $response->assertSee($pendingCorrection3->attendance->work_date->format('Y/m/d'));

        // 申請年月日
        $response->assertSee($pendingCorrection1->created_at->format('Y/m/d'));
        $response->assertSee($pendingCorrection2->created_at->format('Y/m/d'));
        $response->assertSee($pendingCorrection3->created_at->format('Y/m/d'));
    }

    /**
     * @test
     * @group admin-correction-approval
     * 承認済みの修正申請が全て表示されている
     *
     * @return void
     */
    public function 承認済みの修正申請が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 10, 0, 0));

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = Attendance::factory()->create(['user_id' => $user1->id, 'work_date' => Carbon::today()->subDays(3)]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user1->id, 'work_date' => Carbon::today()->subDays(2)]);
        $attendance3 = Attendance::factory()->create(['user_id' => $user2->id, 'work_date' => Carbon::today()->subDays(1)]);
        $attendance4 = Attendance::factory()->create(['user_id' => $user1->id, 'work_date' => Carbon::today()]);

        // 表示されないべき申請 (承認待ち)
        $pendingCorrection = AttendanceCorrection::factory()->create(['attendance_id' => $attendance1->id, 'requester_id' => $user1->id, 'status' => 'pending', 'reason' => '保留中申請', 'created_at' => Carbon::now()->subHours(5)]);

        // 表示されるべき申請 (承認済み)
        $approvedCorrection1 = AttendanceCorrection::factory()->create(['attendance_id' => $attendance2->id, 'requester_id' => $user1->id, 'status' => 'approved', 'reason' => 'ユーザー1の承認済み申請1', 'created_at' => Carbon::now()->subHours(4)]);
        $approvedCorrection2 = AttendanceCorrection::factory()->create(['attendance_id' => $attendance3->id, 'requester_id' => $user2->id, 'status' => 'approved', 'reason' => 'ユーザー2の承認済み申請1', 'created_at' => Carbon::now()->subHours(3)]);
        $approvedCorrection3 = AttendanceCorrection::factory()->create(['attendance_id' => $attendance4->id, 'requester_id' => $user1->id, 'status' => 'approved', 'reason' => 'ユーザー1の承認済み申請2', 'created_at' => Carbon::now()->subHours(2)]);

        $response = $this->get(route('admin.corrections.index', ['status' => 'approved']));

        $response->assertStatus(200);
        // 申請理由
        $response->assertSee($approvedCorrection1->reason);
        $response->assertSee($approvedCorrection2->reason);
        $response->assertSee($approvedCorrection3->reason);
        $response->assertDontSee($pendingCorrection->reason);

        // 申請者名
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);

        // 対象勤怠年月日
        $response->assertSee($approvedCorrection1->attendance->work_date->format('Y/m/d'));
        $response->assertSee($approvedCorrection2->attendance->work_date->format('Y/m/d'));
        $response->assertSee($approvedCorrection3->attendance->work_date->format('Y/m/d'));

        // 申請年月日
        $response->assertSee($approvedCorrection1->created_at->format('Y/m/d'));
        $response->assertSee($approvedCorrection2->created_at->format('Y/m/d'));
        $response->assertSee($approvedCorrection3->created_at->format('Y/m/d'));
    }

    /**
     * @test
     * @group admin-correction-approval
     * 修正申請の詳細内容が正しく表示されている
     *
     * @return void
     */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $correction = AttendanceCorrection::factory()
            ->for($attendance)
            ->withRests(1)
            ->create(['requester_id' => $user->id]);

        $response = $this->get(route('admin.corrections.approve.show', ['attendanceCorrection' => $correction->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($correction->attendance->work_date->format('Y年'));
        $response->assertSee($correction->attendance->work_date->format('m月j日'));
        $response->assertSee($correction->requested_start_time->format('H:i'));
        $response->assertSee($correction->requested_end_time->format('H:i'));
        $response->assertSee($correction->restCorrections->first()->requested_start_time->format('H:i'));
        $response->assertSee($correction->restCorrections->first()->requested_end_time->format('H:i'));
        $response->assertSee($correction->reason);
    }

    /**
     * @test
     * @group admin-correction-approval
     * 修正申請の承認処理が正しく行われる
     *
     * @return void
     */
    public function 修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => Carbon::create(2025, 10, 10, 9, 0, 0),
            'end_time' => Carbon::create(2025, 10, 10, 18, 0, 0),
        ]);
        $correction = AttendanceCorrection::factory()
            ->for($attendance)
            ->withRests(1)
            ->create([
                'requester_id' => $user->id,
                'status' => 'pending',
                'requested_start_time' => Carbon::create(2025, 10, 10, 9, 5, 0),
                'requested_end_time' => Carbon::create(2025, 10, 10, 18, 5, 0),
            ]);

        $response = $this->post(route('admin.corrections.approve', ['attendanceCorrection' => $correction->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success', '申請を承認しました。');

        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '2025-10-10 09:05:00',
            'end_time' => '2025-10-10 18:05:00',
        ]);
    }
}
