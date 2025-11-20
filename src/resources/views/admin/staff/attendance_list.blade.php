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
        <a href="{{ route('admin.attendance.staff.exportCsv', ['user' => $user->id, 'month' => $navigation['currentDate']->format('Y/m')]) }}" class="csv-export-button" id="export-button">CSV出力</a>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportButton = document.getElementById('export-button');

        if (exportButton) {
            exportButton.addEventListener('click', function(event) {
                this.textContent = 'ダウンロード開始';
                this.style.fontSize = '18px';
                this.style.pointerEvents = 'none';
                this.style.opacity = '0.7';
            });
        }
    });
</script>