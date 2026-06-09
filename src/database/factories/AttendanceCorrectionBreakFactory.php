<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrection;

class AttendanceCorrectionBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_correction_id' => AttendanceCorrection::factory(),
            'requested_break_start' => '12:00:00',
            'requested_break_end' => '13:00:00',
        ];
    }
}
