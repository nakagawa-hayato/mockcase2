@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/create.css') }}">
@endsection

@section('content')
<div class="attendance-container">

    <!-- ステータス表示 -->
    <div class="status-box">
        <p>
            @switch($attendance?->status ?? 'off')
                @case('off') 勤務外 @break
                @case('working') 出勤中 @break
                @case('on_break') 休憩中 @break
                @case('finished') 退勤済 @break
                @default 勤務外
            @endswitch
        </p>
    </div>

    <!-- 日付・時間 -->
    <div class="datetime-box">
        <div class="date-box">
            <p>{{ \Carbon\CarbonImmutable::now()->isoFormat('YYYY年MM月DD日(ddd)') }}</p>
        </div>
        <div class="time-box">
            <p>{{ now()->format('H:i') }}</p>
        </div>
    </div>

    <!-- メッセージ -->
    @if(($attendance?->status ?? 'off') === 'finished')
        <div class="message-box">
            <p class="text-green-600 font-bold">お疲れ様でした。</p>
        </div>
    @endif

    <!-- ボタンエリア -->
    <div class="button-box">
        @php
            $status = $attendance?->status ?? 'off';
        @endphp

        @if($status === 'off')
            <form method="POST" action="{{ route('attendance.clockIn') }}">
                @csrf
                <button type="submit" class="btn btn-primary">出勤</button>
            </form>

        @elseif($status === 'working')
            <div class="button-box2">
                <form method="POST" action="{{ route('attendance.clockOut') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">退勤</button>
                </form>

                <form method="POST" action="{{ route('attendance.breakIn') }}">
                    @csrf
                    <button type="submit" class="btn btn-warning">休憩入</button>
                </form>
            </div>

        @elseif($status === 'on_break')
            <form method="POST" action="{{ route('attendance.breakOut') }}">
                @csrf
                <button type="submit" class="btn btn-success">休憩戻</button>
            </form>
        @endif
    </div>
</div>
@endsection
