<?php

namespace Database\Factories;

use App\Models\RestCorrection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class RestCorrectionFactory extends Factory
{
    protected $model = RestCorrection::class;

    public function definition()
    {
        // RestFactoryのロジックに合わせ、一貫性のある休憩時間を生成
        $startTime = Carbon::today()->setTime(12, rand(1, 5), rand(0, 59));
        $endTime = Carbon::today()->setTime(12, rand(55, 59), rand(0, 59));

        return [
            'requested_start_time' => $startTime,
            'requested_end_time' => $endTime,
        ];
    }
}