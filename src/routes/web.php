<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\CorrectionRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider within the "web" middleware group.
|
*/

// 一般ユーザー
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware(['auth','verified'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');

    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::post('/attendance/stamp_correction_request/{attendanceId}', [AttendanceController::class, 'storeCorrectionRequest'])
        ->name('attendance.correction.store');
    Route::get('/attendance/show/{id}', [AttendanceController::class, 'show'])->name('attendance.show');

    // 修正申請一覧（一般ユーザー／管理者共通）
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])
        ->name('correction_request.list');

});

// 管理者
Route::prefix('admin')->group(function () {
    // 認証不要ルート
    Route::middleware('guest.admin')->group(function () {
        Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])->name('admin.login');
        Route::post('/login', [AdminAuthenticatedSessionController::class, 'store']);
    });

    // 認証必要ルート
    Route::middleware(['auth', 'verified', 'is_admin'])->group(function () {
        Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('admin.logout');

        // 管理者トップ（日次勤怠一覧）
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'edit'])->name('admin.attendance.edit');
        Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

        Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.index');
        // 特定スタッフの月次勤怠一覧
        Route::get('/attendance/staff/{id}', [AdminStaffController::class, 'show'])->name('admin.staff.attendance.show');
        // CSVエクスポート
        Route::get('/staff/{id}/export', [AdminStaffController::class, 'exportCsv'])->name('admin.staff.export');
        Route::put('/stamp_correction_request/list/{id}', [CorrectionRequestController::class, 'approve'])
            ->name('correction_request.approve');
    });
});

// Registration
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// ユーザーがメール内リンクをクリックしたとき
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証完了
    return redirect('/attendance'); // 認証後の遷移先
})->middleware(['auth', 'signed'])->name('verification.verify');

// 確認メールの再送信
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '確認メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
