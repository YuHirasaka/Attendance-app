<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

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

    protected $casts = [
        'work_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
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

    public function getBreakMinutes(): int
    {
        return $this->breaks->sum(function ($break) {
            if (!$break->break_start || !$break->break_end){
                return 0;
            }
            return Carbon::parse($break->break_start)
                ->diffInMinutes($break->break_end);
        });
    }

    public function getWorkingMinutes(): int
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        $workMinutes = $this->check_in
            ->diffInMinutes($this->check_out);

        return $workMinutes - $this->getBreakMinutes();
    }

    public function getBreakTimeAttribute(): string
    {
        $minutes = $this->getBreakMinutes();

        $hours = floor($minutes / 60);

        $minutes = $minutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getWorkTimeAttribute(): string
    {
        $minutes = $this->getWorkingMinutes();

        $hours = floor($minutes / 60);

        $minutes = $minutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }
}