@extends('layouts.app_detail')

@section('detail_title', '勤怠詳細（管理者）')

@section('form_action')
    {{ $pendingCorrection ? '#' : route('admin.attendance.update', ['attendance' => $attendance->id]) }}
@endsection

@section('form_method')
    @method('PUT') {{-- PUTメソッドを使用 --}}
@endsection
