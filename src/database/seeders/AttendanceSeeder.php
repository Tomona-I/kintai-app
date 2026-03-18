<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'user@example.com')->first();

        if (!$user) {
            return;
        }

        // 過去30日分（土日を除く平日）の勤怠データを作成
        $today = Carbon::today();

        for ($i = 30; $i >= 1; $i--) {
            $date = $today->copy()->subDays($i);

            // 土日はスキップ
            if ($date->isWeekend()) {
                continue;
            }

            $attendance = Attendance::create([
                'user_id'   => $user->id,
                'date'      => $date->toDateString(),
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
                'notes'     => '',
                'status'    => 0,
            ]);

            // 休憩データを1件作成
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'start'         => '12:00',
                'end'           => '13:00',
            ]);
        }
    }
}
