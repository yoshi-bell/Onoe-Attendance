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
            // for()でattendanceが指定された後に、依存する属性を自動設定する
            if ($correction->attendance_id) {
                $attendance = $correction->attendance;
                $correction->requester_id ??= $attendance->user_id;
                $correction->requested_start_time ??= $attendance->start_time->copy()->addMinutes(rand(-10, 10));
                $correction->requested_end_time ??= $attendance->end_time->copy()->addMinutes(rand(-10, 10));
                $correction->created_at = $this->faker->dateTimeBetween($attendance->work_date, 'now');
                $correction->updated_at = $correction->created_at;
            }
        })
        ->afterCreating(function (AttendanceCorrection $correction) {
            // 承認済みの申請が作成された場合、実際の勤怠データも更新する
            if ($correction->status === 'approved') {
                $attendance = $correction->attendance;
                $attendance->start_time = $correction->requested_start_time;
                $attendance->end_time = $correction->requested_end_time;
                $attendance->save();

                // 休憩データも同様に更新
                $attendance->rests()->delete();
                foreach ($correction->restCorrections as $restCorrection) {
                    $attendance->rests()->create([
                        'start_time' => $restCorrection->requested_start_time,
                        'end_time' => $restCorrection->requested_end_time,
                    ]);
                }
            }
        });
    }

    public function withRests(int $count = 1)
    {
        return $this->has(RestCorrection::factory()->count($count), 'restCorrections');
    }
}