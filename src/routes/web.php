<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RestController;
use App\Http\Controllers\CorrectionRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ルートURL: 認証状態でリダイレクト先を振り分け
Route::get('/', function () {
    if (auth()->check()) {
        // ログイン済みなら勤怠ページへ
        return redirect()->route('attendance');
    }
    // 未ログインならログインページへ
    return redirect()->route('login');
});

// ログイン・メール認証済みユーザー専用のルート
Route::middleware(['auth', 'verified'])->group(function () {
    // 勤怠打刻ページのルート
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');

    // 出勤処理のルート
    Route::post('/attendance/start', [AttendanceController::class, 'startWork'])->name('attendance.start');

    // 退勤処理のルート
    Route::post('/attendance/end', [AttendanceController::class, 'endWork'])->name('attendance.end');

    // 休憩開始処理のルート
    Route::post('/rest/start', [RestController::class, 'start'])->name('rest.start');

    // 休憩終了処理のルート
    Route::post('/rest/end', [RestController::class, 'end'])->name('rest.end');

    // 勤怠一覧ページのルート
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // 勤怠詳細ページのルート
    Route::get('/attendance/detail/{attendance}', [AttendanceController::class, 'show'])->name('attendance.detail');

    // 勤怠修正申請処理のルート
    Route::post('/attendances/{attendance}/correction', [AttendanceController::class, 'storeCorrection'])->name('attendances.correction.store');

    // 申請一覧ページのルート (一般ユーザー)
    Route::get('/attendance/corrections', [CorrectionRequestController::class, 'index'])->name('corrections.index');

    // 今後、他の認証必須ページはここに追加します
});

// 管理者用認証ルート
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [App\Http\Controllers\AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\AdminLoginController::class, 'login']);
        Route::post('/logout', [App\Http\Controllers\AdminLoginController::class, 'logout'])->name('logout');
    });
    
    // 管理者専用機能ルート
    Route::prefix('admin')->middleware(['auth', 'is_admin'])->name('admin.')->group(function () {
            // 勤怠一覧
            Route::get('/attendance/index', [App\Http\Controllers\AdminAttendanceController::class, 'index'])->name('attendance.index');    
        // TODO: 今後、他の管理者用ルートはここに追加する
    });
    
