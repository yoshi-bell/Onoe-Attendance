<?php

namespace Tests\Unit\Services;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Rest;
use App\Models\User;
use App\Services\CorrectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CorrectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private $correctionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->correctionService = new CorrectionService();
    }

    /**
     * @test
     */
    public function 修正申請が正しく保存されること()
    {
        // --- 準備 (Given) ---
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $workDate = $attendance->work_date->format('Y-m-d');

        $requestData = [
            'requested_start_time' => '09:00:00',
            'requested_end_time' => '18:00:00',
            'reason' => '打刻忘れのため',
            'rests' => [
                ['start_time' => '12:00:00', 'end_time' => '13:00:00']
            ]
        ];
        // FormRequestの代わりに通常のRequestを模倣
        $request = new Request($requestData);


        // --- 実行 (When) ---
        $this->correctionService->storeRequest($request, $attendance);

        // --- 検証 (Then) ---
        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'requester_id' => $user->id,
            'reason' => '打刻忘れのため',
            'status' => 'pending',
        ]);

        $correction = AttendanceCorrection::first();
        $this->assertDatabaseHas('rest_corrections', [
            'attendance_correction_id' => $correction->id,
            'requested_start_time' => $workDate . ' 12:00:00',
            'requested_end_time' => $workDate . ' 13:00:00',
        ]);
    }

    /**
     * @test
     */
    public function 修正申請が正しく承認されること()
    {
        // --- 準備 (Given) ---
        $attendance = Attendance::factory()->create([
            'start_time' => '2023-11-20 08:00:00',
            'end_time' => '2023-11-20 17:00:00',
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '2023-11-20 11:00:00',
            'end_time' => '2023-11-20 12:00:00',
        ]);

        $correction = AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_start_time' => '2023-11-20 09:00:00',
            'requested_end_time' => '2023-11-20 18:00:00',
        ]);
        $correction->restCorrections()->create([
            'requested_start_time' => '2023-11-20 12:00:00',
            'requested_end_time' => '2023-11-20 13:00:00',
        ]);


        // --- 実行 (When) ---
        $this->correctionService->approveRequest($correction);

        // --- 検証 (Then) ---

        // 1. 申請ステータスが承認済みに変更されているか
        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => 'approved',
        ]);

        // 2. 勤怠データが申請通りに更新されているか
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '2023-11-20 09:00:00',
            'end_time' => '2023-11-20 18:00:00',
        ]);

        // 3. 休憩データが申請通りに更新されているか
        $this->assertDatabaseHas('rests', [
            'attendance_id' => $attendance->id,
            'start_time' => '2023-11-20 12:00:00',
            'end_time' => '2023-11-20 13:00:00',
        ]);
        // 元の休憩データは削除されているはず
        $this->assertDatabaseMissing('rests', [
            'attendance_id' => $attendance->id,
            'start_time' => '2023-11-20 11:00:00',
        ]);
    }
}
