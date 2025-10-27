@extends('layouts.app')

@section('title', '勤怠打刻')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="status-header">
        {{-- ステータス表示 --}}
        <p class="status-text">{{ $statusText }}</p>
    </div>

    <div class="datetime-panel">
        <p class="date-display">{{ $date }}</p>
        <p class="time-display">{{ $time }}</p>
    </div>

    @if ($hasFinishedWork)
    <div class="completion-message">
        <p>お疲れ様でした。</p>
    </div>
    @else
    <div class="timestamp-container">
        {{-- 勤務開始ボタン --}}
        @if(!$isWorking)
        <form class="timestamp-form" action="{{ route('attendance.start') }}" method="post" novalidate>
            @csrf
            <button type="submit" class="timestamp-button">出勤</button>
        </form>
        @endif

        {{-- 勤務終了ボタン と 休憩開始ボタン --}}
        @if($isWorking && !$isOnBreak)
        <form class="timestamp-form" action="{{ route('attendance.end') }}" method="post" novalidate>
            @csrf
            <button type="submit" class="timestamp-button">退勤</button>
        </form>
        <form class="timestamp-form" action="{{ route('rest.start') }}" method="post" novalidate>
            @csrf
            <button type="submit" class="timestamp-button timestamp-button--rest">休憩入</button>
        </form>
        @endif

        {{-- 休憩終了ボタン --}}
        @if($isWorking && $isOnBreak)
        <form class="timestamp-form" action="{{ route('rest.end') }}" method="post" novalidate>
            @csrf
            <button type="submit" class="timestamp-button timestamp-button--rest">休憩戻</button>
        </form>
        @endif
    </div>
    @endif
</div>
@endsection