<?php

namespace Tests\Unit\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function カレンダーデータが正しく生成されること()
    {
        // --- 準備 (Given) ---
        $user = User::factory()->create();
        $targetMonth = Carbon::create(2023, 11, 1); // 2023年11月 (30日間)

        // テスト用の勤怠データを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2023-11-20', // 月曜日
        ]);

        // --- 実行 (When) ---
        $calendarService = new CalendarService();
        $calendarData = $calendarService->generate($user, $targetMonth);

        // --- 検証 (Then) ---

        // 1. 正しい日数（30日分）のデータが生成されているか
        $this->assertCount(30, $calendarData);

        // 2. 勤怠データがある日（20日）の情報が正しいか
        $day20Data = $calendarData[19]; // 20日は配列の19番目
        $this->assertEquals('11/20(月)', $day20Data['date']);
        $this->assertInstanceOf(Attendance::class, $day20Data['attendance']);
        $this->assertEquals('2023-11-20', $day20Data['attendance']->work_date->format('Y-m-d'));

        // 3. 勤怠データがない日（21日）の情報が正しいか
        $day21Data = $calendarData[20]; // 21日は配列の20番目
        $this->assertEquals('11/21(火)', $day21Data['date']);
        $this->assertNull($day21Data['attendance']);
    }
}
