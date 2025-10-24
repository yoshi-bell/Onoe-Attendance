@extends('layouts.app')

@section('title', '申請一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/correction-list.css') }}">
@endsection

@section('content')
<div class="attendance-list__content">
    <div class="attendance-list__heading">
        <h1>申請一覧（管理者）</h1>
    </div>

    <div class="item-list__tabs">
        <a href="{{ route('admin.corrections.index', ['status' => 'pending']) }}" class="item-list__tab @if($status == 'pending') active @endif">承認待ち</a>
        <a href="{{ route('admin.corrections.index', ['status' => 'approved']) }}" class="item-list__tab @if($status == 'approved') active @endif">承認済み</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($corrections as $correction)
                <tr>
                    <td>
                        @if($correction->status == 'pending')
                            承認待ち
                        @elseif($correction->status == 'approved')
                            承認済み
                        @endif
                    </td>
                    <td>{{ $correction->requester->name }}</td> {{-- 申請者の名前を表示 --}}
                    <td>{{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('Y/m/d') }}</td>
                    <td>{{ $correction->reason }}</td>
                    <td>{{ \Carbon\Carbon::parse($correction->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('admin.corrections.approve.show', ['attendanceCorrection' => $correction->id]) }}">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">該当する申請はありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
