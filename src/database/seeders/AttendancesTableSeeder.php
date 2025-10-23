<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('is_admin', false)->get();
        $today = Carbon::today();

        foreach ($users as $user) {
            // 各ユーザーに対して、昨日から過去60日分のデータを作成
            for ($i = 1; $i <= 60; $i++) {
                $date = $today->copy()->subDays($i);

                // ランダムで休日を設ける (7日間のうちランダムで2日休み)
                if (rand(1, 7) <= 2) { // 1/7の確率で休日
                    continue; // 勤怠データを作成しない
                }

                // 1日1件の勤怠記録を作成
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $date,
                    'start_time' => $date->copy()->setTime(8, rand(45, 59)),
                    'end_time' => $date->copy()->setTime(18, rand(0, 59)),
                ]);

                // その勤怠記録に紐づく休憩記録を1件作成
                Rest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $date->copy()->setTime(12, rand(1, 5)),
                    'end_time' => $date->copy()->setTime(12, rand(55, 59)),
                ]);
            }
        }
    }
}
