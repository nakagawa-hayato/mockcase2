@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="page-title">勤怠詳細</h1>

    {{-- 氏名 --}}
    <div class="form-group">
        <label>氏名</label>
        <p>{{ $attendance->user->name }}</p>
    </div>

    {{-- 日付 --}}
    <div class="form-group">
        <label>日付</label>
        <p>{{ $attendance->date->format('Y年m月d日') }}</p>
    </div>

    {{-- 出勤・退勤 --}}
    <div class="form-group">
        <label>出勤・退勤</label>
        <p>{{ $attendance->clock_in_at?->format('H:i') ?? '--:--' }}
        〜 {{ $attendance->clock_out_at?->format('H:i') ?? '--:--' }}</p>
    </div>

    {{-- 休憩 --}}
    <div class="form-group">
        <label>休憩</label>
        @forelse($attendance->breakTimes as $index => $break)
            <p>{{ $break->start_time?->format('H:i') ?? '--:--' }}
            〜 {{ $break->end_time?->format('H:i') ?? '--:--' }}</p>
        @empty
            <p>休憩なし</p>
        @endforelse
    </div>

    {{-- 備考 --}}
    <div class="form-group">
        <label>備考</label>
        <p>{{ $pendingRequest->reason ?? '---' }}</p>
    </div>

    <div class="notice">
        ※承認待ちのため修正はできません。
    </div>
</div>
@endsection
