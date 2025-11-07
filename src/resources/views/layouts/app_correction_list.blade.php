@extends('layouts.app')

@section('title')
    @yield('list_title')
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/correction-list.css') }}">
@endsection

@section('content')
    <div class="correction-list__content">
        <div class="correction-list__heading">
            <h1 class="correction-list__title">@yield('list_title')</h1>
        </div>

        <div class="item-list__tabs">
            @yield('tabs')
        </div>

        <table class="correction-table">
            <thead>
                <tr>
                    <th class="correction-table__heading">状態</th>
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
                            @if ($correction->status == 'pending')
                                承認待ち
                            @elseif($correction->status == 'approved')
                                承認済み
                            @endif
                        </td>
                        <td>{{ data_get($correction, View::getSection('user_name_field')) }}</td>
                        <td>{{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('Y/m/d') }}</td>
                        <td>{{ $correction->reason }}</td>
                        <td>{{ \Carbon\Carbon::parse($correction->created_at)->format('Y/m/d') }}</td>
                        <td>
                            <a href="{{ route(View::getSection('detail_link_route'), [View::getSection('detail_link_param_name') => data_get($correction, View::getSection('detail_link_param_value_field', 'id'))]) }}">詳細</a>
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
