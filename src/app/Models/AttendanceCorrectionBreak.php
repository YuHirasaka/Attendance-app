<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceCorrectionBreak;


class AttendanceCorrectionBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_break_start',
        'requested_break_end'
    ];

    public function correction()
    {
        return $this->belongsTo(AttendanceCorrectionBreak::class, 'attendance_correction_id');
    }
}
