<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class WorkEndTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group work-end
     * 退勤ボタンが正しく機能する
     *
     * @return void
     */
    public function 退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 9時に出勤
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 9, 0, 0));
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::now(),
            'end_time' => null,
        ]);

        // 1. 出勤中に「退勤」ボタンが表示されていることを確認
        $this->get('/attendance')->assertSee('退勤');

        // 2. 18時に退勤リクエストを送信
        Carbon::setTestNow(Carbon::create(2025, 11, 3, 18, 0, 0));
        $response = $this->post('/attendance/end');

        // 3. データベースの勤怠記録が更新されたことを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'end_time' => Carbon::now(),
        ]);

        // 4. 勤怠ページへのリダイレクトを確認
        $response->assertRedirect('/attendance');

        // 5. ステータスが「退勤済」に変わったことを確認
        $this->get('/attendance')->assertSee('退勤済');
    }

    /**
     * @test
     * @group work-end
     * 退勤時刻が勤怠一覧画面で確認できる
     *
     * @return void
     */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $endTime = Carbon::create(2025, 11, 3, 18, 30, 0);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $endTime->today(),
            'start_time' => $endTime->copy()->subHours(9),
            'end_time' => $endTime,
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($endTime->format('H:i'));
    }
}
