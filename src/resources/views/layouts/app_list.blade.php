@extends('layouts.app')

@section('title')
@yield('list_title')
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css"> {{-- FlatpickrのテーマCSSを追加 --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css"> {{-- MonthSelectプラグインのCSS --}}
@endsection

@section('content')
<div class="attendance-list__content">
    <div class="attendance-list__heading">
        <h1>@yield('list_h1_title')</h1>
    </div>

    <div class="date-navigation">
        @php
        $monthNavParams = json_decode(View::getSection('month_nav_params', '[]'), true);
        if (!is_array($monthNavParams)) {
        $monthNavParams = [];
        }
        @endphp
        <a class="date-nav-button" href="{{ route(View::getSection('month_nav_route_name'), array_merge($monthNavParams, ['month' => $prevMonth])) }}"><i class="fa-solid fa-arrow-left"></i> 前月</a>
        <div class="date-display" id="monthDisplayTrigger">
            <img src="{{ asset('images/icon-calendar.png') }}" alt="カレンダーアイコン">
            <input type="text" class="attendance-date__input" id="monthPicker" value="{{ $currentDate->format('Y/m') }}">
        </div>
        <a class="date-nav-button" href="{{ route(View::getSection('month_nav_route_name'), array_merge($monthNavParams, ['month' => $nextMonth])) }}">翌月 <i class="fa-solid fa-arrow-right"></i></a>
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
                <td>{{ \Carbon\Carbon::parse($dayData['attendance']->start_time)->format('H:i') }}</td>
                <td>{{ $dayData['attendance']->end_time ? \Carbon\Carbon::parse($dayData['attendance']->end_time)->format('H:i') : '' }}</td>
                <td>{{ $dayData['attendance']->total_rest_time }}</td>
                <td>{{ $dayData['attendance']->work_time ?? '' }}</td>
                <td>
                    @if ($dayData['attendance'] && \Carbon\Carbon::parse($dayData['attendance']->work_date)->isBefore($today))
                    <a href="{{ route(View::getSection('detail_link_route'), ['attendance' => $dayData['attendance']->id]) }}">詳細</a>
                    @else
                    詳細
                    @endif
                </td>
                @else
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

    @yield('extra_buttons')
</div>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script> {{-- 公式ドキュメントに合わせてindex.jsに変更 --}}
<script>
    window.onload = function() { // または DOMContentLoaded を使う
        const monthDisplayTrigger = document.getElementById('monthDisplayTrigger');
        const monthPickerInput = document.getElementById('monthPicker');

        const fpMonth = flatpickr(monthPickerInput, {
            locale: flatpickr.l10ns.ja,
            plugins: [new monthSelectPlugin({
                dateFormat: 'Y/m',
            })],
            dateFormat: 'Y/m',
            defaultDate: monthPickerInput.value,
            allowInput: false,
            onChange: function(selectedDates, dateStr, instance) {
                if (dateStr) {
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('month', dateStr);
                    window.location.href = currentUrl.toString();
                }
            }
        });

        // inputがクリックされても開くが、div全体のクリックでも開くように残しておく
        monthDisplayTrigger.addEventListener('click', function() {
            fpMonth.open();
        });
    };
</script>
@endsection