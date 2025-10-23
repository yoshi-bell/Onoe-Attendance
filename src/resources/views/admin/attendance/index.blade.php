@extends('layouts.app')

@section('title', '勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}"> {{-- 一般ユーザー用と共通のCSSを流用 --}}
@endsection

@section('content')
<div class="attendance-list__content">
    <div class="attendance-list__heading">
        <h1>{{ $date->format('Y年n月j日') }}の勤怠</h1>
    </div>

    <div class="date-navigation"> {{-- クラス名は流用 --}}
        <a class="date-nav-button" href="{{ route('admin.attendance.index', ['date' => $prevDate]) }}"><i class="fa-solid fa-arrow-left"></i>前日</a>
        <div class="date-display">
            <img src="{{ asset('images/icon-calendar.png') }}" alt="カレンダーアイコン">
            <span class="current-date">{{ $date->format('Y/m/d') }}</span>
        </div>
        <a class="date-nav-button" href="{{ route('admin.attendance.index', ['date' => $nextDate]) }}">翌日<i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}</td>
                    <td>{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->total_rest_time ?? '0:00' }}</td>
                    <td>{{ $attendance->work_time ?? '' }}</td>
                    <td><a href="#">詳細</a></td> {{-- TODO: 管理者用詳細ページのルートを後で設定 --}}
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection