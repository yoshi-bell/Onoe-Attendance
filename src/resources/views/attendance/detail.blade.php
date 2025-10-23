@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
@php
// 承認待ちの申請の中から最新のものを取得
$pendingCorrection = $attendance->corrections->where('status', 'pending')->last();
@endphp

<div class="detail__container">
    <h1 class="detail__heading">勤怠詳細</h1>

    <div class="detail__card">
        <form class="detail__form" action="{{ $pendingCorrection ? '#' : route('attendances.correction.store', ['attendance' => $attendance->id]) }}" method="POST" novalidate>
            @csrf
            <div class="detail__form-wrapper">
                {{-- 共通表示部分 --}}
                <div class="detail__form-group">
                    <label class="detail__label">氏名</label>
                    <p class="detail__text">{{ $attendance->user->name }}</p>
                </div>
                <div class="detail__form-group">
                    <label class="detail__label">日付</label>
                    <p class="detail__text">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d') }}</p>
                </div>

                {{-- 条件分岐部分 --}}
                @if ($pendingCorrection)
                {{-- 承認待ちの場合の表示 --}}
                <!-- <h2 class="detail__sub-title">申請中の内容</h2> -->
                <div class="detail__form-group">
                    <label class="detail__label">出勤・退勤</label>
                    <p class="detail__text">{{ \Carbon\Carbon::parse($pendingCorrection->requested_start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($pendingCorrection->requested_end_time)->format('H:i') }}</p>
                </div>

                @foreach($pendingCorrection->restCorrections as $index => $restCorrection)
                <div class="detail__form-group">
                    <label class="detail__label">休憩{{ $index + 1 }}</label>
                    <p class="detail__text">{{ \Carbon\Carbon::parse($restCorrection->requested_start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($restCorrection->requested_end_time)->format('H:i') }}</p>
                </div>
                @endforeach

                <div class="detail__form-group">
                    <label class="detail__label">修正理由</label>
                    <p class="detail__text">{{ $pendingCorrection->reason }}</p>
                </div>
                @else
                {{-- 通常の修正フォーム表示 --}}
                <div class="detail__form-group detail__form-group--rest">
                    <label class="detail__label">出勤・退勤</label>
                    <input type="time" class="detail__input detail__input--time" name="requested_start_time" value="{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}">
                    <span class="detail__separator">〜</span>
                    <input type="time" class="detail__input detail__input--time" name="requested_end_time" value="{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}">
                </div>
                <div class="detail__error-wrapper">
                    @error('requested_start_time')<p class="detail__error">{{ $message }}</p>@enderror
                    @error('requested_end_time')<p class="detail__error">{{ $message }}</p>@enderror
                </div>

                <!-- <h2 class="detail__sub-title">休憩時間</h2> -->
                @foreach($attendance->rests as $index => $rest)
                <div class="detail__form-group detail__form-group--rest">
                    <label class="detail__label" for="rest_start_time_{{ $index }}">休憩{{ $index + 1 }}</label>
                    <input type="time" class="detail__input detail__input--time" id="rest_start_time_{{ $index }}" name="rests[{{ $rest->id }}][start_time]" value="{{ \Carbon\Carbon::parse($rest->start_time)->format('H:i') }}">
                    <span class="detail__separator">〜</span>
                    <input type="time" class="detail__input detail__input--time" id="rest_end_time_{{ $index }}" name="rests[{{ $rest->id }}][end_time]" value="{{ $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '' }}">
                </div>
                <div class="detail__error-wrapper detail__error-wrapper--rest">
                    @error('rests.'. $rest->id . '.start_time')<p class="detail__error">{{ $message }}</p>@enderror
                    @error('rests.'. $rest->id . '.end_time')<p class="detail__error">{{ $message }}</p>@enderror
                </div>
                @endforeach

                <div class="detail__form-group detail__form-group--rest">
                    <label class="detail__label" for="new_rest_start_time">休憩追加</label>
                    <input type="time" class="detail__input detail__input--time" id="new_rest_start_time" name="rests[new][start_time]">
                    <span class="detail__separator">〜</span>
                    <input type="time" class="detail__input detail__input--time" id="new_rest_end_time" name="rests[new][end_time]">
                </div>
                <div class="detail__error-wrapper detail__error-wrapper--rest">
                    @error('rests.new.start_time')<p class="detail__error">{{ $message }}</p>@enderror
                    @error('rests.new.end_time')<p class="detail__error">{{ $message }}</p>@enderror
                </div>

                <div class="detail__form-group">
                    <label class="detail__label" for="reason">備考</label>
                    <textarea class="detail__textarea" id="reason" name="reason" rows="3"></textarea>
                </div>
                <div class="detail__error-wrapper">
                    @error('reason')<p class="detail__error">{{ $message }}</p>@enderror
                </div>
                @endif
            </div>

            <div class="detail__button-wrapper">
                @if ($pendingCorrection)
                <p class="detail__pending-message">*承認待ちのため修正はできません</p>
                @else
                <button type="submit" class="detail__button">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection