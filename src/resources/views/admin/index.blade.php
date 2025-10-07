@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="form day-attendance-list">
    <h1 class="content__heading day-attendance-list__heading">{{ $date->format('Y年n月j日') }}（{{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }}）の勤怠</h1>


    <div class="content-nav">
        <a class="content-nav__link" href="{{ route('admin.attendance.index', ['date' => $prevDate]) }}">
            <img src="{{ asset('img/arrow.png') }}" alt="タイトル画像" class="image-arrow__left" />
            前日
        </a>
        <div class="content-nav__date-label">
            <img src="{{ asset('img/calendar.png') }}" alt="タイトル画像" class="image-calendar" />
            <span class="content-nav__label">{{ $date->format('Y/m/d') }}</span>
        </div>
        <a class="content-nav__link" href="{{ route('admin.attendance.index', ['date' => $nextDate]) }}">
            翌日
            <img src="{{ asset('img/arrow.png') }}" alt="タイトル画像" class="image-arrow__right" />
        </a>
    </div>

    <table class="list-table">
        <tr class="list-table__row">
            <th class="list-table__label">名前</th>
            <th class="list-table__label">出勤</th>
            <th class="list-table__label">退勤</th>
            <th class="list-table__label">休憩</th>
            <th class="list-table__label">合計</th>
            <th class="list-table__label">詳細</th>
        </tr>
        @forelse ($attendances as $attendance)
            <tr class="list-table__row">
                {{-- 氏名 --}}
                <td class="list-table__data">{{ $attendance?->user->name }}</td>

                {{-- 出勤 --}}
                <td class="list-table__data">{{ $attendance?->clock_in_hm }}</td>

                {{-- 退勤 --}}
                <td class="list-table__data">{{ $attendance?->clock_out_hm }}</td>

                {{-- 休憩 --}}
                <td class="list-table__data">{{ $attendance?->break_hm }}</td>

                {{-- 勤務時間 --}}
                <td class="list-table__data">{{ $attendance?->work_hm }}</td>
                <td class="list-table__data">
                    <a href="{{ route('admin.attendance.edit', $attendance->id) }}" class="list-table__detail-btn">
                        詳細
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">この日の勤怠データはありません</td>
            </tr>
        @endforelse
    </table>
</div>
@endsection
