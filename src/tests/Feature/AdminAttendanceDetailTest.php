<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group admin-attendance-detail
     * 勤怠詳細画面に表示されるデータが選択したものになっている
     *
     * @return void
     */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2025, 11, 10),
            'start_time' => Carbon::create(2025, 11, 10, 9, 0, 0),
            'end_time' => Carbon::create(2025, 11, 10, 18, 0, 0),
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::create(2025, 11, 10, 12, 0, 0),
            'end_time' => Carbon::create(2025, 11, 10, 13, 0, 0),
        ]);
        $attendance->refresh();

        $response = $this->get(route('admin.attendance.show', ['attendance' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('m月j日'));
        $response->assertSee('value="' . $attendance->start_time->format('H:i') . '"', false);
        $response->assertSee('value="' . $attendance->end_time->format('H:i') . '"', false);
        $response->assertSee('value="' . $attendance->rests->first()->start_time->format('H:i') . '"', false);
        $response->assertSee('value="' . $attendance->rests->first()->end_time->format('H:i') . '"', false);
    }

    /**
     * @test
     * @group admin-attendance-detail
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function 出勤時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $attendance = Attendance::factory()->create();

        $invalidData = [
            'requested_start_time' => '18:00',
            'requested_end_time' => '09:00',
            'reason' => 'テスト理由', // このテストでは理由は必須ではないが念のため
        ];

        $response = $this->put(route('admin.attendance.update', ['attendance' => $attendance->id]), $invalidData);

        $response->assertSessionHasErrors(['requested_end_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * @test
     * @group admin-attendance-detail
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function 休憩開始時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $attendance = Attendance::factory()->create();

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

        $response = $this->put(route('admin.attendance.update', ['attendance' => $attendance->id]), $invalidData);

        $response->assertSessionHasErrors(['rests.0.start_time' => '休憩時間が不適切な値です']);
    }

    /**
     * @test
     * @group admin-attendance-detail
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function 休憩終了時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $attendance = Attendance::factory()->create();

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

        $response = $this->put(route('admin.attendance.update', ['attendance' => $attendance->id]), $invalidData);

        $response->assertSessionHasErrors(['rests.0.end_time' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * @test
     * @group admin-attendance-detail
     * 備考欄が未入力の場合のエラーメッセージが表示される
     *
     * @return void
     */
    public function 備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $attendance = Attendance::factory()->create();

        $invalidData = [
            'requested_start_time' => '09:00',
            'requested_end_time' => '18:00',
            'reason' => '', // 備考欄を空にする
        ];

        $response = $this->put(route('admin.attendance.update', ['attendance' => $attendance->id]), $invalidData);

        $response->assertSessionHasErrors(['reason' => '備考を記入してください']);
    }
}
