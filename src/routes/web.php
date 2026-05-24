<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StaffAttendanceController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['auth', 'verified'])->group(function (){
    Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check_in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check_out');
    Route::post('/attendance/break-start', [AttendanceController::class, 'startBreak'])->name('attendance.break_start');
    Route::post('/attendance/break-end', [AttendanceController::class, 'endBreak'])->name('attendance.break_end');
    Route::get('attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/stamp_correction_request', [AttendanceController::class, 'store'])->name('attendance_correction.store');
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])->name('correction.index');
    Route::get('/correction/{attendanceCorrection_id}', [CorrectionRequestController::class, 'show'])->name('correction.show');
});

Route::middleware(['auth:admin', 'admin'])->prefix('admin')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/attendance/create', [AdminAttendanceController::class, 'create'])->name('admin.attendance.create');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'edit'])->name('admin.attendance.edit');
    Route::post('/attendance/save', [AdminAttendanceController::class, 'save'])->name('admin.attendance.save');
    Route::get('/staff/list' , [StaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/attendance/staff/{id}', [StaffAttendanceController::class, 'index'])->name('admin.staff.attendance.index');
});

Route::get('admin/login', function () {
    return view('admin.login');
})->name('admin.login');

Route::post('login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

Route::get('/email/verify', function() {
    return view('auth.verify-email');
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('attendance.show');
})->middleware(['signed'])->name('verification.verify');

Route::post('email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['throttle:6,1'])->name('verification.send');