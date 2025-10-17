@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list__content">
    <div class="month-navigation">
        <a class="month-nav-button" href="{{ route('attendance.list', ['month' => $prevMonth]) }}">&lt;</a>
        <span class="current-month">{{ $currentDate->format('Y年m月') }}</span>
        <a class="month-nav-button" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">&gt;</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>曜日</th>
                <th>勤務開始</th>
                <th>勤務終了</th>
                <th>休憩時間</th>
                <th>勤務時間</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($calendarData as $dayData)
                <tr>
                    <td>{{ $dayData['date'] }}</td>
                    <td>{{ $dayData['dayOfWeek'] }}</td>
                    @if ($dayData['attendance'])
                        {{-- データが存在する場合 --}}
                        <td>{{ \Carbon\Carbon::parse($dayData['attendance']->start_time)->format('H:i') }}</td>
                        <td>{{ $dayData['attendance']->end_time ? \Carbon\Carbon::parse($dayData['attendance']->end_time)->format('H:i') : '' }}</td>
                        <td>{{ $dayData['attendance']->total_rest_time }}</td>
                        <td>{{ $dayData['attendance']->work_time }}</td>
                    @else
                        {{-- データが存在しない場合 --}}
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
