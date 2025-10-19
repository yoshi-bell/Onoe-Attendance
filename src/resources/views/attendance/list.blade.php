@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list__content">
    <div class="attendance-list__heading">
        <h1>勤怠一覧</h1>
    </div>

    <div class="month-navigation">
        <a class="month-nav-button" href="{{ route('attendance.list', ['month' => $prevMonth]) }}"><i class="fa-solid fa-arrow-left"></i> 前月</a>
        <div class="month-display">
            <img src="{{ asset('images/icon-calendar.png') }}" alt="カレンダーアイコン">
            <span class="current-month">{{ $currentDate->format('Y/m') }}</span>
        </div>
        <a class="month-nav-button" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">翌月 <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($calendarData as $dayData)
                <tr>
                    <td>{{ $dayData['date'] }}</td>
                    @if ($dayData['attendance'])
                        {{-- データが存在する場合 --}}
                        <td>{{ \Carbon\Carbon::parse($dayData['attendance']->start_time)->format('H:i') }}</td>
                        <td>{{ $dayData['attendance']->end_time ? \Carbon\Carbon::parse($dayData['attendance']->end_time)->format('H:i') : '-' }}</td>
                        <td>{{ $dayData['attendance']->total_rest_time }}</td>
                        <td>{{ $dayData['attendance']->work_time ?? '-' }}</td>
                    @else
                        {{-- データが存在しない場合 (<td>の数を修正) --}}
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endif
                    {{-- 「詳細」リンクを@ifの外に出し、常に表示する --}}
                    <td><a href="#">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
