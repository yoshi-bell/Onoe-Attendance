@extends('layouts.app_correction_list')

@section('list_title', '申請一覧')

@section('tabs')
    <a href="{{ route('corrections.index', ['status' => 'pending']) }}" class="item-list__tab @if($status == 'pending') active @endif">承認待ち</a>
    <a href="{{ route('corrections.index', ['status' => 'approved']) }}" class="item-list__tab @if($status == 'approved') active @endif">承認済み</a>
@endsection

@section('user_name_field', 'attendance.user.name')

@section('detail_link_route', 'attendance.detail')

@section('detail_link_param_name', 'attendance')