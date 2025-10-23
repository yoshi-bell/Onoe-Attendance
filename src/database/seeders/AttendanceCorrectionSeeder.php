<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;
use Carbon\Carbon;

class AttendanceCorrectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('is_admin', false)->get();

        foreach ($users as $user) {
            // 各ユーザーの勤怠データを取得
            $attendances = Attendance::where('user_id', $user->id)->get();

            foreach ($attendances as $attendance) {
                // 勤怠データごとに約10%の確率で修正申請を作成
                if (rand(0, 9) === 0) {
                    $requestedStartTime = Carbon::parse($attendance->start_time)->addMinutes(rand(-30, 30));
                    $requestedEndTime = Carbon::parse($attendance->end_time)->addMinutes(rand(-30, 30));

                    $status = rand(0, 1) ? 'pending' : 'approved'; // 承認待ちか承認済みをランダムに決定


                    $correction = AttendanceCorrection::create([
                        'attendance_id' => $attendance->id,
                        'requester_id' => $user->id,

                        'requested_start_time' => $requestedStartTime,
                        'requested_end_time' => $requestedEndTime,
                        'reason' => '遅延のため',
                        'status' => $status,
                    ]);

                    // 休憩修正申請もランダムで作成
                    if (rand(0, 1)) {
                        $restStartTime = Carbon::parse($requestedStartTime)->addHours(rand(2, 4));
                        $restEndTime = $restStartTime->copy()->addMinutes(rand(30, 60));
                        RestCorrection::create([
                            'attendance_correction_id' => $correction->id,
                            'requested_start_time' => $restStartTime,
                            'requested_end_time' => $restEndTime,
                        ]);
                    }
                }
            }
        }
    }
}
