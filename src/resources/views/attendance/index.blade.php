@extends('layouts.app')

@section('title', '勤怠打刻')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="status-header">
        <p class="status-text">{{ $attendanceData->status->statusText }}</p>
    </div>

    <div class="datetime-panel">
        <p class="date-display">{{ $attendanceData->date }}</p>
        <p class="time-display">{{ $attendanceData->time }}</p>
    </div>

    @if ($attendanceData->status->hasFinishedWork)
    <div class="completion-message">
        <p>お疲れ様でした。</p>
    </div>
    @else
    <div class="timestamp-container">
        @if(!$attendanceData->status->isWorking)
        <form class="timestamp-form" action="{{ route('attendance.start') }}" method="post" novalidate>
            @csrf
            <button type="submit" class="timestamp-button">出勤</button>
        </form>
        @endif

        @if($attendanceData->status->isWorking && !$attendanceData->status->isOnBreak)
        <form class="timestamp-form" action="{{ route('attendance.end') }}" method="post" novalidate>
            @csrf
            <button type="submit" class="timestamp-button">退勤</button>
        </form>
        <form class="timestamp-form" action="{{ route('rest.start') }}" method="post" novalidate>
            @csrf
            <button type="submit" class="timestamp-button timestamp-button--rest">休憩入</button>
        </form>
        @endif

        @if($attendanceData->status->isWorking && $attendanceData->status->isOnBreak)
        <form class="timestamp-form" action="{{ route('rest.end') }}" method="post" novalidate>
            @csrf
            <button type="submit" class="timestamp-button timestamp-button--rest">休憩戻</button>
        </form>
        @endif
    </div>
    @endif
</div>
@endsection