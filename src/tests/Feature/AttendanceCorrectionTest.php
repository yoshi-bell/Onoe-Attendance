<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group attendance-correction
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function 出勤時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $invalidData = [
            'requested_start_time' => '18:00',
            'requested_end_time' => '09:00',
            'reason' => 'テスト理由',
        ];

        $response = $this->post(route('attendances.correction.store', ['attendance' => $attendance->id]), $invalidData);

        $response->assertSessionHasErrors(['requested_end_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * @test
     * @group attendance-correction
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function 休憩開始時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $invalidData = [
            'requested_start_time' => '09:00',
            'requested_end_time' => '18:00',
            'reason' => 'テスト理由',
            'rests' => [
                [
                    'start_time' => '19:00', // 退勤時刻より後
                    'end_time' => '19:30',
                ]
            ]
        ];

        $response = $this->post(route('attendances.correction.store', ['attendance' => $attendance->id]), $invalidData);

        $response->assertSessionHasErrors(['rests.0.start_time' => '休憩時間が不適切な値です']);
    }

    /**
     * @test
     * @group attendance-correction
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function 休憩終了時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $invalidData = [
            'requested_start_time' => '09:00',
            'requested_end_time' => '18:00',
            'reason' => 'テスト理由',
            'rests' => [
                [
                    'start_time' => '17:00',
                    'end_time' => '18:30', // 退勤時刻より後
                ]
            ]
        ];

        $response = $this->post(route('attendances.correction.store', ['attendance' => $attendance->id]), $invalidData);

        $response->assertSessionHasErrors(['rests.0.end_time' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * @test
     * @group attendance-correction
     * 備考欄が未入力の場合のエラーメッセージが表示される
     *
     * @return void
     */
    public function 備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $invalidData = [
            'requested_start_time' => '09:00',
            'requested_end_time' => '18:00',
            'reason' => '', // 備考欄を空にする
        ];

        $response = $this->post(route('attendances.correction.store', ['attendance' => $attendance->id]), $invalidData);

        $response->assertSessionHasErrors(['reason' => '備考を記入してください']);
    }

    /**
     * @test
     * @group attendance-correction
     * 修正申請処理が実行される
     *
     * @return void
     */
    public function 修正申請処理が実行される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => Carbon::create(2025, 10, 10, 9, 0, 0),
            'end_time' => Carbon::create(2025, 10, 10, 18, 0, 0),
        ]);

        $validData = [
            'requested_start_time' => '09:05',
            'requested_end_time' => '18:05',
            'reason' => '交通機関の遅延のため',
            'rests' => [
                [
                    'start_time' => '12:00',
                    'end_time' => '13:00',
                ]
            ]
        ];

        $response = $this->post(route('attendances.correction.store', ['attendance' => $attendance->id]), $validData);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'requester_id' => $user->id,
            'reason' => '交通機関の遅延のため',
        ]);

        $this->assertDatabaseHas('rest_corrections', [
            'requested_start_time' => $attendance->work_date->format('Y-m-d') . ' 12:00:00',
            'requested_end_time' => $attendance->work_date->format('Y-m-d') . ' 13:00:00',
        ]);

        // 管理者としてログインし、申請が確認できることを検証
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $correction = $attendance->corrections()->latest()->first();

        // 申請一覧画面で確認
        $adminListResponse = $this->get(route('admin.corrections.index'));
        $adminListResponse->assertStatus(200);
        $adminListResponse->assertSee($user->name);
        $adminListResponse->assertSee($correction->reason);

        // 承認画面で詳細を確認
        $adminApproveResponse = $this->get(route('admin.corrections.approve.show', ['attendanceCorrection' => $correction->id]));
        $adminApproveResponse->assertStatus(200);
        $adminApproveResponse->assertSee($user->name);
        $adminApproveResponse->assertSee($correction->requested_start_time->format('H:i'));
        $adminApproveResponse->assertSee($correction->requested_end_time->format('H:i'));
        $adminApproveResponse->assertSee($correction->attendance->work_date->format('Y年'));
        $adminApproveResponse->assertSee($correction->attendance->work_date->format('m月j日'));
    }

    /**
     * @test
     * @group attendance-correction
     * 「承認待ち」にログインユーザーが行った申請が全て表示されていること
     *
     * @return void
     */
    public function 「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance1 = Attendance::factory()->create(['user_id' => $user->id, 'work_date' => Carbon::today()->subDays(2)]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user->id, 'work_date' => Carbon::today()->subDays(1)]);
        $attendance3 = Attendance::factory()->create(['user_id' => $user->id, 'work_date' => Carbon::today()]);

        // 表示されるべき申請 (承認待ち)
        $pendingCorrection1 = AttendanceCorrection::factory()->create(['attendance_id' => $attendance1->id, 'requester_id' => $user->id, 'status' => 'pending', 'reason' => '表示されるべき理由1']);
        $pendingCorrection2 = AttendanceCorrection::factory()->create(['attendance_id' => $attendance2->id, 'requester_id' => $user->id, 'status' => 'pending', 'reason' => '表示されるべき理由2']);

        // 表示されないべき申請 (承認済み)
        $approvedCorrection = AttendanceCorrection::factory()->create(['attendance_id' => $attendance3->id, 'requester_id' => $user->id, 'status' => 'approved', 'reason' => '表示されないべき理由（承認済）']);

        // 表示されないべき申請 (他ユーザー)
        $otherUser = User::factory()->create();
        $otherUserAttendance = Attendance::factory()->create(['user_id' => $otherUser->id, 'work_date' => Carbon::today()->subDays(3)]);
        $otherUserCorrection = AttendanceCorrection::factory()->create(['attendance_id' => $otherUserAttendance->id, 'requester_id' => $otherUser->id, 'status' => 'pending', 'reason' => '表示されないべき理由（他ユーザー）']);

        $response = $this->get(route('corrections.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee($pendingCorrection1->reason);
        $response->assertSee($pendingCorrection2->reason);
        $response->assertDontSee($approvedCorrection->reason);
        $response->assertDontSee($otherUserCorrection->reason);
    }

    /**
     * @test
     * @group attendance-correction
     * 「承認済み」に管理者が承認した修正申請が全て表示されている
     *
     * @return void
     */
    public function 「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance1 = Attendance::factory()->create(['user_id' => $user->id, 'work_date' => Carbon::today()->subDays(2)]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user->id, 'work_date' => Carbon::today()->subDays(1)]);
        $attendance3 = Attendance::factory()->create(['user_id' => $user->id, 'work_date' => Carbon::today()]);

        // 表示されないべき申請 (承認待ち)
        $pendingCorrection = AttendanceCorrection::factory()->create(['attendance_id' => $attendance1->id, 'requester_id' => $user->id, 'status' => 'pending', 'reason' => '表示されないべき理由（承認待ち）']);

        // 表示されるべき申請 (承認済み)
        $approvedCorrection1 = AttendanceCorrection::factory()->create(['attendance_id' => $attendance2->id, 'requester_id' => $user->id, 'status' => 'approved', 'reason' => '表示されるべき理由1（承認済）']);
        $approvedCorrection2 = AttendanceCorrection::factory()->create(['attendance_id' => $attendance3->id, 'requester_id' => $user->id, 'status' => 'approved', 'reason' => '表示されるべき理由2（承認済）']);

        $response = $this->get(route('corrections.index', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee($approvedCorrection1->reason);
        $response->assertSee($approvedCorrection2->reason);
        $response->assertDontSee($pendingCorrection->reason);
    }

    /**
     * @test
     * @group attendance-correction
     * 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
     *
     * @return void
     */
    public function 各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $correction = AttendanceCorrection::factory()->create(['attendance_id' => $attendance->id, 'requester_id' => $user->id, 'status' => 'pending']);

        // 申請一覧画面を開き、詳細リンクが存在することを確認
        $listResponse = $this->get(route('corrections.index'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee(route('attendance.detail', ['attendance' => $attendance->id]));

        // 詳細リンクを直接叩いて詳細画面に遷移することを検証
        $detailResponse = $this->get(route('attendance.detail', ['attendance' => $attendance->id]));

        $detailResponse->assertStatus(200);
        $detailResponse->assertViewIs('attendance.detail');
        $detailResponse->assertSee($user->name);
    }
}