<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

class AttendanceCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'requested_check_in' => '10:00:00',
            'requested_check_out' => '18:00:00',
            'reason' => '休日出勤です',
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}
