<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RestController;

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

    // 今後、他の認証必須ページはここに追加します
});
