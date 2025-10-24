@extends('layouts.app_detail')

@section('detail_title', '勤怠詳細')

@section('form_action')
    {{ $pendingCorrection ? '#' : route('attendances.correction.store', ['attendance' => $attendance->id]) }}
@endsection

@section('form_method')
    {{-- 一般ユーザーはPOSTで修正申請 --}}
@endsection