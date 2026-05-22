<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('role', 'user')->get();

        $months = [
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->startOfMonth(),
        ];

        foreach ($users as $user) {
            foreach ($months as $month) {
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();
                $today = Carbon::today();

                for ($date = $start->copy(); $date <= $end; $date->addDay()) {
                    if ($date > $today) {
                        break;
                    }

                    if ($date->isWeekend()) {
                        continue;
                    }

                    $attendance = Attendance::factory()->create([
                        'user_id' => $user->id,
                        'work_date' => $date->copy()->format('Y-m-d'),
                    ]);

                    AttendanceBreak::factory()->create([
                        'attendance_id' => $attendance->id,
                    ]);
                }
            }
        }
    }
}
