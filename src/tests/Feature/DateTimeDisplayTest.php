<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class DateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group datetime
     * 現在の日時情報がUIと同じ形式で出力されている
     *
     * @return void
     */
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $now = Carbon::now();
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $date = $now->format('Y年m月d日') . '(' . $week[$now->dayOfWeek] . ')';
        $time = $now->format('H:i');

        $response->assertSee($date);
        $response->assertSee($time);
    }
}
