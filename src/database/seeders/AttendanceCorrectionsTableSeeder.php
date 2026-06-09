<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;



class AttendanceCorrectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {
            $attendances = Attendance::where('user_id', $user->id)
            ->take(9)
            ->get();

            foreach ($attendances->take(3) as $attendance) {
                AttendanceCorrection::create([
                    'attendance_id' => $attendance->id,
                    'requested_check_in' => '09:15',
                    'requested_check_out' => '18:15',
                    'reason' => '電車遅延のため',
                    'status' => 'approved',
                ]);
            }

            foreach ($attendances->skip(6) as $attendance) {
                AttendanceCorrection::create([
                    'attendance_id' => $attendance->id,
                    'requested_check_in' => '09:15',
                    'requested_check_out' => '18:15',
                    'reason' => '電車遅延のため',
                    'status' => 'pending',
                ]);
            }
        }
    }
}
