@extends('layouts.app_list')

@section('list_title', 'スタッフ別勤怠一覧')

@section('list_h1_title')
    {{ $user->name }}さんの勤怠
@endsection

@section('month_nav_route_name', 'admin.attendance.staff.showAttendance')

@section('month_nav_params')
    @php echo json_encode(['user' => $user->id]); @endphp
@endsection

@section('detail_link_route', 'admin.attendance.show')

@section('extra_buttons')
    <div class="csv-export-button-wrapper">
        {{-- TODO: CSV出力機能の実装 --}}
        <form action="#" method="GET">
            <button type="submit" class="csv-export-button">CSV出力</button>
        </form>
    </div>
@endsection