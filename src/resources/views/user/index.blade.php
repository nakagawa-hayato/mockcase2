@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="page-title">勤怠一覧</h1>

    {{-- 月ナビ --}}
    <div class="month-nav">
        <a class="month-nav__link" href="{{ route('attendance.index', ['ym' => $prevYm]) }}">←先月</a>
        <span class="month-nav__label">{{ $displayLabel }}</span>
        <a class="month-nav__link" href="{{ route('attendance.index', ['ym' => $nextYm]) }}">翌月→</a>
    </div>

    {{-- テーブル --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    {{-- 日付 --}}
                    <td>{{ $row['attendance']?->date_label ?? ($row['carbon']->format('m/d') . '（' . ['日','月','火','水','木','金','土'][$row['carbon']->dayOfWeek] . '）') }}</td>

                    {{-- 出勤 --}}
                    <td>{{ $row['attendance']?->clock_in_hm }}</td>

                    {{-- 退勤 --}}
                    <td>{{ $row['attendance']?->clock_out_hm }}</td>

                    {{-- 休憩 --}}
                    <td>{{ $row['attendance']?->break_hm }}</td>

                    {{-- 勤務時間 --}}
                    <td>{{ $row['attendance']?->work_hm }}</td>


                    {{-- 詳細（常に表示） --}}
                    <td>
                        <a href="{{ route('attendance.show', ['id' => $row['attendance']?->id ?? 0]) }}" class="btn-small">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
