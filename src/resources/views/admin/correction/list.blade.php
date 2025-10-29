@extends('layouts.app_correction_list')

@section('list_title', '申請一覧（管理者）')

@section('tabs')
    <a href="{{ route('admin.corrections.index', ['status' => 'pending']) }}" class="item-list__tab @if($status == 'pending') active @endif">承認待ち</a>
    <a href="{{ route('admin.corrections.index', ['status' => 'approved']) }}" class="item-list__tab @if($status == 'approved') active @endif">承認済み</a>
@endsection

@section('user_name_field', 'requester.name')

@section('detail_link_route', 'admin.corrections.approve.show')

@section('detail_link_param_name', 'attendanceCorrection')
