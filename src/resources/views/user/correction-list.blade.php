@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="correction-list">
    <h1 class="page-title">申請一覧</h1>

    {{-- タブ切り替え --}}
    <div class="tabs">
        <a href="{{ route('attendance.correction.list', ['status' => 'pending']) }}"
           class="{{ $status === 'pending' ? 'active' : '' }}">
           承認待ち
        </a>
        <a href="{{ route('attendance.correction.list', ['status' => 'approved']) }}"
           class="{{ $status === 'approved' ? 'active' : '' }}">
           承認済み
        </a>
    </div>

    {{-- 一覧テーブル --}}
    <table class="table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
                <tr>
                    <td>{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                    <td>{{ $request->attendance->user->name ?? '-' }}</td>
                    <td>{{ $request->attendance->date?->format('Y-m-d') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('attendance.show', $request->attendance->id) }}" class="btn btn-sm">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">申請はありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ページネーション --}}
    {{ $requests->links() }}
</div>
@endsection
