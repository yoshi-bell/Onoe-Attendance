@extends('layouts.app')

@section('title', '勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css"> {{-- FlatpickrのテーマCSSを追加 --}}
@endsection

@section('content')
<div class="attendance-list__content">
    <div class="attendance-list__heading">
        <h1>{{ $date->format('Y年n月j日') }}の勤怠</h1>
    </div>

    <div class="date-navigation">
        <a class="date-nav-button" href="{{ route('admin.attendance.index', ['date' => $prevDate]) }}"><i class="fa-solid fa-arrow-left"></i>前日</a>
        <div class="date-display" id="dateDisplayTrigger">
            <img src="{{ asset('images/icon-calendar.png') }}" alt="カレンダーアイコン">
            <input type="text" class="attendance-date__input" id="datePicker" value="{{ $date->format('Y/m/d') }}">
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
                    <td>
                        @if (\Carbon\Carbon::parse($attendance->work_date)->isBefore($today))
                            <a href="{{ route('admin.attendance.show', ['attendance' => $attendance->id]) }}">詳細</a>
                        @else
                            詳細
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dateDisplayTrigger = document.getElementById('dateDisplayTrigger');
        const datePickerInput = document.getElementById('datePicker');

        const fp = flatpickr(datePickerInput, {
            locale: flatpickr.l10ns.ja, // 日本語化
            dateFormat: 'Y/m/d', // inputに設定される値の形式
            defaultDate: datePickerInput.value, // 初期日付
            allowInput: false, // ユーザーによる直接入力は許可しない
            onChange: function(selectedDates, dateStr, instance) {
                if (dateStr) {
                    window.location.href = `{{ route('admin.attendance.index') }}?date=${dateStr}`;
                }
            }
        });

        dateDisplayTrigger.addEventListener('click', function () {
            fp.open(); // Flatpickrのカレンダーを開く
        });
    });
</script>
@endsection