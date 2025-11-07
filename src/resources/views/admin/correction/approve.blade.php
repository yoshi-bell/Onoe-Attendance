@extends('layouts.app')

@section('title', '修正申請承認')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}"> {{-- 勤怠詳細画面と共通のCSSを流用 --}}
@endsection

@section('content')
<div class="detail__container">
    <div class="detail__heading">
        <h1 class="detail__title">勤怠詳細</h1>
    </div>
    <div class="detail__card">
        <form class="detail__form" action="{{ route('admin.corrections.approve', ['attendanceCorrection' => $attendanceCorrection->id]) }}" method="POST" novalidate>
            @csrf
            <div class="detail__form-wrapper">
                <div class="detail__form-group">
                    <label class="detail__label">氏名</label>
                    <p class="detail__text--name">{{ $attendanceCorrection->requester->name }}</p>
                </div>
                <div class="detail__form-group">
                    <label class="detail__label">日付</label>
                    <p class="detail__text--date"><span class="detail__date-year">{{ \Carbon\Carbon::parse($attendanceCorrection->attendance->work_date)->format('Y年') }}</span>
                    <span class="detail__date-monthday">{{ \Carbon\Carbon::parse($attendanceCorrection->attendance->work_date)->format('m月j日') }}</span>
                    </p>
                </div>
                <div class="detail__form-group">
                    <label class="detail__label">出勤・退勤</label>
                    <p class="detail__text--time">
                        {{ \Carbon\Carbon::parse($attendanceCorrection->requested_start_time)->format('H:i') }}
                    </p>
                    <span class="detail__separator">〜</span>
                    <p class="detail__text--time">
                        {{ \Carbon\Carbon::parse($attendanceCorrection->requested_end_time)->format('H:i') }}
                    </p>
                </div>

                @if($attendanceCorrection->restCorrections->isNotEmpty())
                @foreach($attendanceCorrection->restCorrections as $index => $restCorrection)
                <div class="detail__form-group">
                    <label class="detail__label">休憩{{ $index + 1 }}</label>
                    <p class="detail__text--time">
                        {{ \Carbon\Carbon::parse($restCorrection->requested_start_time)->format('H:i') }}
                    </p>
                    <span class="detail__separator">〜</span>
                    <p class="detail__text--time">
                        {{ \Carbon\Carbon::parse($restCorrection->requested_end_time)->format('H:i') }}
                    </p>
                </div>
                @endforeach
                @else
                <div class="detail__form-group">
                    <label class="detail__label">休憩</label>
                    <p class="detail__text--empty">なし</p>
                </div>
                @endif

                <div class="detail__form-group">
                    <label class="detail__label">備考</label>
                    <p class="detail__text--reason">{{ $attendanceCorrection->reason }}</p>
                </div>
            </div>

            <div class="detail__button-wrapper">
                @if ($attendanceCorrection->status == 'pending')
                <button type="submit" class="detail__button">承認</button>
                @else
                <button type="button" class="detail__button detail__button--approved" disabled>承認済み</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection