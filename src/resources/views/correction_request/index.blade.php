@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
<div class="correction-list">
    <h1 class="page-title">修正申請一覧</h1>

    @foreach($requests as $req)
        <div class="correction-item">
            <p>氏名：{{ $req->attendance->user->name }}</p>
            <p>日付：{{ $req->attendance->date->format('Y年m月d日') }}</p>
            <p>出退勤：{{ $req->attendance->clock_in_at?->format('H:i') ?? '--:--' }} 〜 {{ $req->attendance->clock_out_at?->format('H:i') ?? '--:--' }}</p>
            <p>理由：{{ $req->reason }}</p>

            @if(auth()->user()->isAdmin())
                {{-- 管理者画面 --}}
                @if($req->status === 'pending')
                    <form action="{{ route('correction_request.approve', $req->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-primary">承認</button>
                    </form>
                @else
                    <span class="approved-label">承認済み</span>
                @endif
            @else
                {{-- 一般ユーザー画面 --}}
                @if($req->status === 'pending')
                    <div class="notice">※承認待ちのため修正はできません。</div>
                @else
                    <span class="approved-label">承認済み</span>
                @endif
            @endif
        </div>
    @endforeach

    {{ $requests->links() }}
</div>
@endsection
