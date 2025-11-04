<?php

namespace Database\Factories;

use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use App\Models\RestCorrection;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectionFactory extends Factory
{
    protected $model = AttendanceCorrection::class;

    public function definition()
    {
        return [
            'reason' => '遅延のため',
            'status' => $this->faker->randomElement(['pending', 'approved']),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (AttendanceCorrection $correction) {
            if ($correction->attendance_id) {
                $attendance = $correction->attendance;
                $correction->requester_id ??= $attendance->user_id;
                $correction->requested_start_time ??= $attendance->start_time->copy()->addMinutes(rand(-10, 10));
                $correction->requested_end_time ??= $attendance->end_time->copy()->addMinutes(rand(-10, 10));
            }
        });
    }

    public function withRests(int $count = 1)
    {
        return $this->has(RestCorrection::factory()->count($count), 'restCorrections');
    }
}