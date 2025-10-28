@extends('layouts.app_list')

@section('list_title', '勤怠一覧')

@section('list_h1_title', '勤怠一覧')

@section('month_nav_route_name', 'attendance.list')

@section('month_nav_params')
    @php echo json_encode([]); @endphp
@endsection

@section('detail_link_route', 'attendance.detail')

@section('extra_buttons')
    {{-- 一般ユーザーには追加ボタンなし --}}
@endsection