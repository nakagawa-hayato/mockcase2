@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/correction-detail.css') }}">
@endsection

@section('content')
<div class="form correction-detail-form">
    <h1 class="content__heading correction-detail-form__heading">勤怠詳細</h1>

    <table class="correction-detail-form__table">
        <tr class="correction-detail-form__row">
            <th class="correction-detail-form__label">名前</th>
            <td class="correction-detail-form__name">{{ $correction->attendance->user->name }}</td>
        </tr>
        <tr class="correction-detail-form__row">
            <th class="correction-detail-form__label">日付</th>
            <td class="correction-detail-form__data-group">
                <span class="correction-detail-form__data">{{ $correction->attendance?->date_year_label ?? '' }}</span>
                <span class="correction-detail-form__data">{{ $correction->attendance?->date_month_day_label ?? '' }}</span>
            </td>
        </tr>
        <tr class="correction-detail-form__row">
            <th class="correction-detail-form__label">出勤・退勤</th>
            <td class="correction-detail-form__data-group">
                <span class="correction-detail-form__data">{{ $correction->clock_in_at ? \Carbon\Carbon::parse($correction->clock_in_at)->format('H:i') : '--:--' }}</span>
                〜
                <span class="correction-detail-form__data">{{ $correction->clock_out_at ? \Carbon\Carbon::parse($correction->clock_out_at)->format('H:i') : '--:--' }}</span>
            </td>
        </tr>
        @if($correction->breakCorrections && $correction->breakCorrections->count() > 0)
            @foreach($correction->breakCorrections as $break)
                <tr class="correction-detail-form__row">
                    <th class="correction-detail-form__label">{{ $loop->first ? '休憩' : '休憩' . $loop->iteration }}</th>
                    <td class="correction-detail-form__data-group">
                        <span class="correction-detail-form__data">{{ $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : '--:--' }}</span>
                        〜
                        <span class="correction-detail-form__data">{{ $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : '--:--' }}</span>
                    </td>
                </tr>
            @endforeach
        @else
            <tr class="correction-detail-form__row">
                <th class="correction-detail-form__label">休憩</th>
                <td class="correction-detail-form__data">―</td>
            </tr>
        @endif
        <tr class="correction-detail-form__row">
            <th class="correction-detail-form__label">備考</th>
            <td class="correction-detail-form__remarks">{{ $correction->reason }}</td>
        </tr>
    </table>

    {{-- 管理者 or 一般ユーザーの表示分岐 --}}
    @if(auth()->user()->isAdmin())
        {{-- 管理者 --}}
        @if($correction->status === 'pending')
            <form action="{{ route('correction_request.approve', $correction) }}" method="POST" class="correction-detail-form__approve-form">
                @csrf
                @method('PUT')
                <button type="submit" class="btn correction-detail-form__approve-btn">承認する</button>
            </form>
        @else
            <div class="correction-detail-form__approved-label">承認済み</div>
        @endif
    @else
        {{-- 一般ユーザー --}}
        @if($correction->status === 'pending')
            <div class="correction-detail-form__notice">*承認待ちのため修正できません。</div>
        @else
            <div class="correction-detail-form__approved-label">承認済み</div>
        @endif
    @endif
</div>
@endsection
