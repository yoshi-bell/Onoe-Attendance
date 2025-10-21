<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // TODO: 全ユーザーの勤怠一覧データを取得するロジックを実装
        return view('admin.attendance.index');
    }
}
