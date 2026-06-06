<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function export(Request $request)
    {
        $userId = $request->user_id;
        $month = Carbon::parse($request->month);

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $days = collect();

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $days->push($date->copy());
        }

        $attendances = Attendance::with('breaks')
            ->where('user_id', $userId)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)
                    ->format('Y-m-d');
            });

        $csvHeader = [
            '日付',
            '出勤',
            '退勤',
            '休憩',
            '合計',
        ];

        $response = new StreamedResponse(function () use ($csvHeader, $days, $attendances) {
            $createCsvFile = fopen('php://output', 'w');

            mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);

            fputcsv($createCsvFile, $csvHeader);

            foreach ($days as $day) {
                $attendance = $attendances->get($day->format('Y-m-d'));

                $csv = [
                    $day->format('Y/m/d'),
                    $attendance?->check_in?->format('H:i'),
                    $attendance?->check_out?->format('H:i'),
                    $attendance?->break_time,
                    $attendance?->work_time,
                ];

                mb_convert_variables('SJIS-win', 'UTF-8', $csv);
                fputcsv($createCsvFile, $csv);
            }

            fclose($createCsvFile);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="staff_attendance.csv"',
        ]);

        return $response;
    }
}
