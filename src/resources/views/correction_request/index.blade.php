@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
<link rel="stylesheet" href="{{ asset('css/correction-list.css') }}">
@endsection

@section('content')
<div class="form correction-list__form">
    <h1 class="content__heading correction-list__heading">申請一覧</h1>

    {{-- タブ切り替え --}}
    <div class="list-tabs">
        <a href="{{ route('correction_request.list', ['status' => 'pending']) }}"
           class="list__tab {{ $status === 'pending' ? 'active-tab' : '' }}">
           承認待ち
        </a>
        <a href="{{ route('correction_request.list', ['status' => 'approved']) }}"
           class="list__tab {{ $status === 'approved' ? 'active-tab' : '' }}">
           承認済み
        </a>
    </div>

    {{-- 一覧テーブル --}}
    <table class="list-table">
        <tr class="list-table__row">
            <th class="list-table__label">状態</th>
            <th class="list-table__label">名前</th>
            <th class="list-table__label">対象日時</th>
            <th class="list-table__label">申請理由</th>
            <th class="list-table__label">申請日時</th>
            <th class="list-table__label">詳細</th>
        </tr>
        @forelse($requests as $request)
            <tr class="list-table__row">
                <td class="list-table__data">{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                <td class="list-table__data">{{ str_replace('　', '', $request->attendance->user->name ?? '-') }}</td>
                <td class="list-table__data date">{{ $request->attendance->date?->format('Y/m/d') ?? '-' }}</td>
                <td class="list-table__data">{{ $request->reason }}</td>
                <td class="list-table__data date">{{ $request->created_at->format('Y/m/d') }}</td>
                <td class="list-table__data">
                    <a href="{{ route('correction_request.show', $request->id) }}" class="list-table__detail-btn correction-list__table-btn">
                        詳細
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">申請はありません</td>
            </tr>
        @endforelse
    </table>
</div>
@endsection
