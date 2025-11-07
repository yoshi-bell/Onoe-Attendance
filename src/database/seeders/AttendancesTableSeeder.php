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
            for ($i = 1; $i <= 60; $i++) {
                $date = $today->copy()->subDays($i);

                // ランダムで休日を設ける (7日間のうちランダムで2日休み)
                if (rand(1, 7) <= 2) {
                    continue;
                }

                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $date,
                    'start_time' => $date->copy()->setTime(8, rand(45, 59)),
                    'end_time' => $date->copy()->setTime(18, rand(0, 59)),
                ]);

                Rest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $date->copy()->setTime(12, rand(1, 5)),
                    'end_time' => $date->copy()->setTime(12, rand(55, 59)),
                ]);
            }
        }
    }
}
