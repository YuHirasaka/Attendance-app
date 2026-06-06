<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_id',
        'requested_break_start',
        'requested_break_end'
    ];

    protected $casts = [
        'requested_break_end' => 'datetime',
        'requested_break_start' => 'datetime'
    ];

    public function correction()
    {
        return $this->belongsTo(AttendanceCorrection::class, 'attendance_correction_id');
    }
}
