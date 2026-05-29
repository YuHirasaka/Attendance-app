<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class StaffAttendanceController extends Controller
{
    public function index($id)
    {
        $user = User::findOrFail($id);

        $currentMonth = request('month')
        ? Carbon::parse(request('month'))
        : Carbon::now();

        $prevMonth = $currentMonth->copy()
            ->subMonth()
            ->format('Y-m');

        $nextMonth = $currentMonth->copy()
            ->addMonth()
            ->format('Y-m');

        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        $days = collect();

        for ($date = $start; $date <= $end; $date->addDay()) {
            $days->push($date->copy());
        }

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)
                    ->format('Y-m-d');
            });

        return view('admin.staff.attendance', compact(
            'user',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'days',
            'attendances'
        ));
    }
}
