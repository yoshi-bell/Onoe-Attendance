@extends('layouts.app')

@section('title')
@yield('detail_title')
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail__container">
    <div class="detail__heading">
        <h1 class="detail__title">@yield('detail_title')</h1>
    </div>


    <div class="detail__card">
        <form class="detail__form" action="@yield('form_action')" method="POST" novalidate>
            @csrf
            @yield('form_method')

            <div class="detail__form-wrapper">
                <div class="detail__form-group">
                    <label class="detail__label">氏名</label>
                    <p class="detail__text--name">{{ $attendance->user->name }}</p>
                </div>
                <div class="detail__form-group">
                    <label class="detail__label">日付</label>
                    <p class="detail__text--date">
                        <span class="detail__date-year">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                        <span class="detail__date-monthday">{{ \Carbon\Carbon::parse($attendance->work_date)->format('m月j日') }}</span>
                    </p>
                </div>

                @if ($pendingCorrection)
                <div class="detail__form-group">
                    <label class="detail__label">出勤・退勤</label>
                    <span class="detail__text detail__text--time">{{ \Carbon\Carbon::parse($pendingCorrection->requested_start_time)->format('H:i') }} </span>
                    <span class="detail__separator">〜</span>
                    <span class="detail__text detail__text--time">{{ \Carbon\Carbon::parse($pendingCorrection->requested_end_time)->format('H:i') }}</span>
                </div>

                @foreach($pendingCorrection->restCorrections as $index => $restCorrection)
                <div class="detail__form-group">
                    <label class="detail__label">休憩{{ $index + 1 }}</label>
                    <span class="detail__text detail__text--time">{{ \Carbon\Carbon::parse($restCorrection->requested_start_time)->format('H:i') }}</span>
                    <span class="detail__separator">〜</span>
                    <span class="detail__text detail__text--time">{{ \Carbon\Carbon::parse($restCorrection->requested_end_time)->format('H:i') }}</span>
                </div>
                @endforeach

                <div class="detail__form-group">
                    <label class="detail__label">備考</label>
                    <p class="detail__text--reason">{{ $pendingCorrection->reason }}</p>
                </div>
                @else
                <div class="detail__form-group">
                    <label class="detail__label" for="attendance_start_time">出勤・退勤</label>
                    <input type="time" class="detail__input detail__input--time" id="attendance_start_time" name="requested_start_time" value="{{ old('requested_start_time', \Carbon\Carbon::parse($attendance->start_time)->format('H:i')) }}">
                    <span class="detail__separator">〜</span>
                    <input type="time" class="detail__input detail__input--time" name="requested_end_time" value="{{ old('requested_end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                </div>
                <div class="detail__error-wrapper">
                    @error('requested_start_time')<p class="form__error">{{ $message }}</p>@enderror
                    @error('requested_end_time')<p class="form__error">{{ $message }}</p>@enderror
                </div>

                @foreach($attendance->rests as $index => $rest)
                <div class="detail__form-group">
                    <label class="detail__label" for="rest_start_time_{{ $index }}">休憩{{ $index + 1 }}</label>
                    <input type="time" class="detail__input detail__input--time" id="rest_start_time_{{ $index }}" name="rests[{{ $rest->id }}][start_time]" value="{{ old('rests.' . $rest->id . '.start_time', \Carbon\Carbon::parse($rest->start_time)->format('H:i')) }}">
                    <span class="detail__separator">〜</span>
                    <input type="time" class="detail__input detail__input--time" id="rest_end_time_{{ $index }}" name="rests[{{ $rest->id }}][end_time]" value="{{ old('rests.' . $rest->id . '.end_time', $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '') }}">
                </div>
                <div class="detail__error-wrapper">
                    @error('rests.'. $rest->id . '.start_time')<p class="form__error">{{ $message }}</p>@enderror
                    @error('rests.'. $rest->id . '.end_time')<p class="form__error">{{ $message }}</p>@enderror
                </div>
                @endforeach

                <div class="detail__form-group">
                    <label class="detail__label" for="new_rest_start_time">休憩{{ ($index ?? -1) + 2 }}</label>
                    <input type="time" class="detail__input detail__input--time" name="rests[new][start_time]" value="{{ old('rests.new.start_time') }}">
                    <span class="detail__separator">〜</span>
                    <input type="time" class="detail__input detail__input--time" id="new_rest_end_time" name="rests[new][end_time]" value="{{ old('rests.new.end_time') }}">
                </div>
                <div class="detail__error-wrapper">
                    @error('rests.new.start_time')<p class="form__error">{{ $message }}</p>@enderror
                    @error('rests.new.end_time')<p class="form__error">{{ $message }}</p>@enderror
                </div>

                <div class="detail__form-group detail__form-group--reason">
                    <label class="detail__label" for="reason">備考</label>
                    <textarea class="detail__textarea" id="reason" name="reason" rows="3">{{ old('reason') }}</textarea>
                </div>
                <div class="detail__error-wrapper">
                    @error('reason')<p class="form__error">{{ $message }}</p>@enderror
                </div>
                @endif

                @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif
            </div>

            <div class="detail__button-wrapper">

                @if ($pendingCorrection)
                <p class="detail__pending-message">*承認待ちの申請があるため修正はできません</p>
                @else
                <button type="submit" class="detail__button">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection