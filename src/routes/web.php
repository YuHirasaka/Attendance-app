<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\AttendanceController;


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
});

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