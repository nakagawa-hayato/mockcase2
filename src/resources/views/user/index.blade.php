@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="form month-attendance-list">
    <h1 class="content__heading month-attendance-list__heading">勤怠一覧</h1>

    {{-- 月ナビ --}}
    <div class="content-nav">
        <a class="content-nav__link" href="{{ route('attendance.index', ['ym' => $prevYm]) }}">
            <img src="{{ asset('img/arrow.png') }}" alt="タイトル画像" class="image-arrow__left" />
            先月</a>
        <div class="content-nav__date-label">
            <img src="{{ asset('img/calendar.png') }}" alt="タイトル画像" class="image-calendar" />
            <span class="content-nav__label">{{ $displayLabel }}</span>
        </div>
        <a class="content-nav__link" href="{{ route('attendance.index', ['ym' => $nextYm]) }}">
            翌月
            <img src="{{ asset('img/arrow.png') }}" alt="タイトル画像" class="image-arrow__right" />
        </a>
    </div>

    {{-- テーブル --}}
    <table class="list-table">
        <tr class="list-table__row">
            <th class="list-table__label date">日付</th>
            <th class="list-table__label">出勤</th>
            <th class="list-table__label">退勤</th>
            <th class="list-table__label">休憩</th>
            <th class="list-table__label">合計</th>
            <th class="list-table__label">詳細</th>
        </tr>
        @foreach ($rows as $row)
            <tr class="list-table__row">
                {{-- 日付 --}}
                <td class="list-table__data date">{{ $row['attendance']?->date_label ?? ($row['carbon']->format('m/d') . '（' . ['日','月','火','水','木','金','土'][$row['carbon']->dayOfWeek] . '）') }}</td>

                {{-- 出勤 --}}
                <td class="list-table__data">{{ $row['attendance']?->clock_in_hm }}</td>

                {{-- 退勤 --}}
                <td class="list-table__data">{{ $row['attendance']?->clock_out_hm }}</td>

                {{-- 休憩 --}}
                <td class="list-table__data">{{ $row['attendance']?->break_hm }}</td>

                {{-- 勤務時間 --}}
                <td class="list-table__data">{{ $row['attendance']?->work_hm }}</td>


                {{-- 詳細（常に表示） --}}
                <td class="list-table__data">
                    <a href="{{ route('attendance.edit', ['id' => $row['attendance']?->id ?? 0]) }}" class="list-table__detail-btn">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection
