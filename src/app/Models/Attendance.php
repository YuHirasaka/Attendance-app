<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceBreak;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_DONE = '退勤済';
    const STATUS_BREAK = '休憩中';
    const STATUS_WORKING = '出勤中';
    const STATUS_NOT_WORKING = '勤務外';

    protected $fillable = [
        'work_date',
        'check_in',
        'check_out',
        'note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function Correction(){
        return $this->hasOne(AttendanceCorrection::class);
    }

    public function status()
    {
        if($this->check_out){
            return self::STATUS_DONE;
        } elseif ($this->breaks()
                    ->whereNotNull('break_start')
                    ->whereNull('break_end')
                    ->exists()) {
            return self::STATUS_BREAK;
        } elseif ($this->check_in) {
            return self::STATUS_WORKING;
        } else {
            return self::STATUS_NOT_WORKING;
        }
    }
}