@extends('layouts.app')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list__content">
    <div class="attendance-list__heading">
        <h1>{{ $user->name }}さんの勤怠</h1>
    </div>

    <div class="date-navigation">
        <a class="date-nav-button" href="{{ route('admin.attendance.staff.showAttendance', ['user' => $user->id, 'month' => $prevMonth]) }}"><i class="fa-solid fa-arrow-left"></i> 前月</a>
        <div class="date-display">
            <img src="{{ asset('images/icon-calendar.png') }}" alt="カレンダーアイコン">
            <span class="current-date">{{ $currentDate->format('Y/m') }}</span>
        </div>
        <a class="date-nav-button" href="{{ route('admin.attendance.staff.showAttendance', ['user' => $user->id, 'month' => $nextMonth]) }}">翌月 <i class="fa-solid fa-arrow-right"></i></a>
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
                        <td>{{ $dayData['attendance']->end_time ? \Carbon\Carbon::parse($dayData['attendance']->end_time)->format('H:i') : '' }}</td>
                        <td>{{ $dayData['attendance']->total_rest_time }}</td>
                        <td>{{ $dayData['attendance']->work_time ?? '' }}</td>
                        <td>
                            @if ($dayData['attendance'] && \Carbon\Carbon::parse($dayData['attendance']->work_date)->isBefore($today))
                                <a href="{{ route('admin.attendance.show', ['attendance' => $dayData['attendance']->id]) }}">詳細</a>
                            @else
                                詳細
                            @endif
                        </td>
                    @else
                        {{-- データが存在しない場合 --}}
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>詳細</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="csv-export-button-wrapper">
        {{-- TODO: CSV出力機能の実装 --}}
        <form action="#" method="GET">
            <button type="submit" class="csv-export-button" >CSV出力</button>
        </form>
    </div>
</div>
@endsection
