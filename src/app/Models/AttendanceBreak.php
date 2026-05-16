<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'break_start',
        'break_end'
    ];

    protected $casts = [
        'break_start' => 'dateTime',
        'break_end' => 'dateTime'
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
