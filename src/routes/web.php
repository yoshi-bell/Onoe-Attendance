<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RestController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\CorrectionRequestController as AdminCorrectionRequestController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ルートURL: 認証状態でリダイレクト先を振り分け
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('attendance');
    }
    return redirect()->route('login');
});

// 一般ユーザー用ルート (認証・メール認証必須)
Route::middleware(['auth', 'verified', 'is_general_user'])->group(function () {
    // 勤怠打刻ページ
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');

    // 出勤処理
    Route::post('/attendance/start', [AttendanceController::class, 'startWork'])->name('attendance.start');

    // 退勤処理
    Route::post('/attendance/end', [AttendanceController::class, 'endWork'])->name('attendance.end');

    // 休憩開始処理
    Route::post('/rest/start', [RestController::class, 'start'])->name('rest.start');

    // 休憩終了処理
    Route::post('/rest/end', [RestController::class, 'end'])->name('rest.end');

    // 勤怠一覧ページ (一般ユーザー)
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // 勤怠詳細ページ (一般ユーザー)
    Route::get('/attendance/detail/{attendance}', [AttendanceController::class, 'show'])->name('attendance.detail');

    // 勤怠修正申請処理
    Route::post('/attendances/correction/{attendance}', [AttendanceController::class, 'storeCorrection'])->name('attendances.correction.store');

    // 申請一覧ページ (一般ユーザー)
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])->name('corrections.index');
});

// 管理者用認証ルート
Route::prefix('admin')->name('admin.')->group(function () {
    // ログインページ
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');

    // ログイン処理
    Route::post('/login', [AdminLoginController::class, 'login']);

    // ログアウト処理
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
});

// 管理者専用機能ルート
Route::prefix('admin')->middleware(['auth', 'is_admin'])->name('admin.')->group(function () {
    // 日次勤怠一覧ページ (管理者)
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠詳細ページ (管理者)
    Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])->name('attendance.show');

    // 勤怠情報更新処理 (管理者)
    Route::put('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendance.update');

    // 申請一覧ページ (管理者)
    Route::get('/stamp_correction_request/list', [AdminCorrectionRequestController::class, 'index'])->name('corrections.index');

    // 修正申請承認ページ
    Route::get('/stamp_correction_request/approve/{attendanceCorrection}', [AdminCorrectionRequestController::class, 'show'])->name('corrections.approve.show');

    // 修正申請承認処理
    Route::post('/stamp_correction_request/approve/{attendanceCorrection}', [AdminCorrectionRequestController::class, 'approve'])->name('corrections.approve');

    // スタッフ一覧ページ
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.list');

    // スタッフ別勤怠一覧ページ
    Route::get('/attendance/staff/{user}', [AdminStaffController::class, 'showAttendance'])->name('attendance.staff.showAttendance');

    // CSVエクスポート処理
    Route::get('/attendance/staff/{user}/export-csv', [AdminStaffController::class, 'exportCsv'])->name('attendance.staff.exportCsv');
});
