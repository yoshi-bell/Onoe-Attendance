<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
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
            $attendances = Attendance::where('user_id', $user->id)
                ->where('work_date', '>=', Carbon::now()->subMonth()->startOfMonth())
                ->get();

            foreach ($attendances as $attendance) {
                // 勤怠データごとに約10%の確率で修正申請を作成
                if (rand(0, 9) === 0) {
                    AttendanceCorrection::factory()
                        ->for($attendance)
                        ->state(['requester_id' => $user->id])
                        ->withRests(rand(0, 2))
                        ->create();
                }
            }
        }
    }
}
