@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="page-title">勤怠詳細</h1>

    <form action="{{ route('attendance.correction.store', $attendance?->id ) }}" method="POST" class="attendance-form">
        @csrf

        {{-- 氏名（固定表示） --}}
        <div class="form-group">
            <label>氏名</label>
            <p>{{ $attendance?->user->name ?? '' }}</p>
        </div>

        {{-- 日付（固定表示） --}}
        <div class="form-group">
            <label>日付</label>
            <P>{{ $attendance?->date?->format('Y年m月d日') ?? '' }}</p>
        </div>

        {{-- 出勤・退勤 --}}
        <div class="form-group">
            <label>出勤・退勤</label>
            <input type="time" name="clock_in_at" value="{{ $attendance?->clock_in_hm }}">
            〜
            <input type="time" name="clock_out_at" value="{{ $attendance?->clock_out_hm }}">
        </div>

        {{-- 休憩（複数行） --}}
        @foreach($attendance->breakTimes as $index => $break)
            <div class="form-group">
                <label>{{ $index === 0 ? '休憩' : '休憩 ' . ($index+1) }}</label>
                <input type="time" name="breaks[{{ $index }}][start_time]" value="{{ $break->start_time?->format('H:i') }}">
                〜
                <input type="time" name="breaks[{{ $index }}][end_time]" value="{{ $break->end_time?->format('H:i') }}">
            </div>
        @endforeach

        {{-- 備考 --}}
        <div class="form-group">
            <label>備考</label>
            <textarea name="reason">{{ old('reason')}}</textarea>
        </div>


        {{-- 送信ボタン --}}
        <button type="submit" class="btn">修正</button>

    </form>


</div>
@endsection
