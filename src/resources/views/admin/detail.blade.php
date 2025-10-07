@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="form detail-form">
    <h1 class="content__heading attendance-detail-form__heading">勤怠詳細</h1>

    <form action="{{ route('admin.attendance.update', $attendance?->id ?? 0 ) }}" method="POST" class="attendance-form">
        @csrf
        @method('PUT')

        <div class="detail-form__inner">
            {{-- 名前 --}}
            <div class="detail-form__group">
                <label class="detail-form__label">名前</label>
                <span class="detail-form__name">{{ $attendance?->user->name ?? '-' }}</span>
            </div>

            {{-- 日付 --}}
            <div class="detail-form__group">
                <label class="detail-form__label">日付</label>
                <div class="detail-form__date">
                    <span>{{ $attendance?->date_year_label ?? '' }}</span>
                    <span>{{ $attendance?->date_month_day_label ?? '' }}</span>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="detail-form__group">
                <label class="detail-form__label" for="clock_at">出勤・退勤</label>
                <div class="detail-form__inputs-wrapper">
                    <div class="detail-form__inputs">
                        <input class="detail-form__input" type="time" name="clock_in_at" id="clock_at"
                            value="{{ old('clock_in_at', $attendance?->clock_in_hm) }}">
                        〜
                        <input class="detail-form__input" type="time" name="clock_out_at" 
                        value="{{ old('clock_out_at', $attendance?->clock_out_hm) }}">
                    </div>
                    @error('clock_in_at')
                        <div class="form__error">
                            {{ $message }}
                        </div>
                    @enderror
                    @error('clock_out_at')
                        <div class="form__error">
                            {{ $message }}
                        </div>
                    @enderror
                    @error('clock_in_out')
                        <div class="form__error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            {{-- 休憩（複数対応） --}}
            @foreach($attendance->breakTimes as $index => $break)
                <div class="detail-form__group">
                    <label class="detail-form__label"  for="break-time">{{ $index === 0 ? '休憩' : '休憩 ' . ($index+1) }}</label>
                    <div class="detail-form__inputs-wrapper">
                        <div class="detail-form__inputs">
                            <input class="detail-form__input" type="time" name="breaks[{{ $index }}][start_time]" id="break-time" value="{{ old("breaks.$index.start_time", $break->start_time?->format('H:i')) }}">
                            〜
                            <input class="detail-form__input" type="time" name="breaks[{{ $index }}][end_time]" value="{{ old("breaks.$index.end_time", $break->end_time?->format('H:i')) }}">
                        </div>
                        @error("breaks.$index.start_end")
                            <div class="form__error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            @endforeach

            {{-- ★新規入力用の空行を 1 行追加 --}}
            @php
                $nextIndex = $attendance->breakTimes->count();
            @endphp
            <div class="detail-form__group">
                <label class="detail-form__label" for="break-time">
                    {{ $nextIndex === 0 ? '休憩' : '休憩 ' . ($nextIndex+1) }}
                </label>
                <div class="detail-form__inputs-wrapper">
                    <div class="detail-form__inputs">
                        <input class="detail-form__input" type="time" 
                            name="breaks[{{ $nextIndex }}][start_time]" 
                            id="break-time" >
                        〜
                        <input class="detail-form__input" type="time" 
                            name="breaks[{{ $nextIndex }}][end_time]" >
                    </div>
                    @error("breaks.$nextIndex.start_end")
                        <div class="form__error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            {{-- 備考 --}}
            <div class="detail-form__group">
                <label class="detail-form__label" for="textarea">備考</label>
                <div class="detail-form__inputs-wrapper">
                    <textarea class="detail-form__textarea" name="reason" id="textarea">{{ old('reason', $attendance?->reason) }}</textarea>
                    @error('reason')
                        <div class="form__error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- 送信 --}}
        <button type="submit" class="btn detail-form__btn">修正</button>
    </form>
</div>
@endsection
