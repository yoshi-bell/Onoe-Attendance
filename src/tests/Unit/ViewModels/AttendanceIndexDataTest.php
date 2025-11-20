<?php

namespace Tests\Unit\ViewModels;

use App\Models\User;
use App\Services\AttendanceStatusService;
use App\ViewModels\AttendanceIndexData;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceIndexDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function データが正しく準備されること()
    {
        // 一貫したテストのために時間を固定
        Carbon::setTestNow(Carbon::create(2023, 11, 20, 10, 30, 0)); // 月曜日, 10:30

        $user = User::factory()->create();

        $viewModel = new AttendanceIndexData($user);

        // 日付と時刻を確認
        $this->assertEquals('2023年11月20日(月)', $viewModel->date);
        $this->assertEquals('10:30', $viewModel->time);

        // ステータスオブジェクトを確認
        $this->assertInstanceOf(AttendanceStatusService::class, $viewModel->status);
    }
}
