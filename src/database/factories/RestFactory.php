<?php

namespace Database\Factories;

use App\Models\Rest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class RestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 休憩開始時刻を12:01〜12:05の間に設定
        $startTime = Carbon::today()->setTime(12, rand(1, 5), rand(0, 59));

        // 休憩終了時刻を12:55〜12:59の間に設定
        $endTime = Carbon::today()->setTime(12, rand(55, 59), rand(0, 59));

        return [
            // attendance_id と work_date はシーダー側で指定します
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }
}
