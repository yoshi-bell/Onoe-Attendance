<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // このファクトリーは単体では使わず、シーダーから日付を指定して呼び出すことを想定
        $workDate = $this->faker->dateTimeThisMonth();
        $startTime = Carbon::instance($workDate)->setTime(9, rand(0, 59));
        $endTime = $startTime->copy()->addHours(9);

        return [
            'work_date' => $startTime->toDateString(),
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }
}
