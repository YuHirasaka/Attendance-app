<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionBreak;

class AttendanceCorrectionBreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $corrections = AttendanceCorrection::all();

        foreach ($corrections as $correction) {
            AttendanceCorrectionBreak::create([
                'attendance_correction_id' => $correction->id,
                'requested_break_start' => '12:00',
                'requested_break_end' => '13:00',
            ]);
        }
    }
}
