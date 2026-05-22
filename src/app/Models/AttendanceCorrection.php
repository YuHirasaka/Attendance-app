<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionBreak;
use App\Models\User;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'requested_check_in',
        'requested_check_out',
        'reason',
        'status',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'requested_check_in' => 'datetime',
        'requested_check_out' => 'datetime',
    ];

    public function Attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceCorrectionBreak::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'pending' => '承認待ち',
            'approved' => '承認済み'
        ][$this->status];
    }
}
