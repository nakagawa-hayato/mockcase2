@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="page-title">{{ $user->name }}さんの勤怠（{{ $displayLabel }}）</h1>

    {{-- 月ナビ --}}
    <div class="month-nav mb-3">
        <a href="{{ route('admin.staff.attendance.show', ['id'=>$user->id,'ym'=>$prevYm]) }}">←先月</a>
        <span class="mx-2">{{ $displayLabel }}</span>
        <a href="{{ route('admin.staff.attendance.show', ['id'=>$user->id,'ym'=>$nextYm]) }}">翌月→</a>
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
                <td>{{ $row['attendance']?->date_label ?? $row['date']->format('m/d') . '（' . ['日','月','火','水','木','金','土'][$row['date']->dayOfWeek] . '）' }}</td>
                <td>{{ $row['attendance']?->clock_in_hm }}</td>
                <td>{{ $row['attendance']?->clock_out_hm }}</td>
                <td>{{ $row['attendance']?->break_hm }}</td>
                <td>{{ $row['attendance']?->work_hm }}</td>
                <td>
                    <a href="{{ route('admin.attendance.edit', $row['attendance']?->id ?? 0) }}" class="btn-small">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- CSV出力 --}}
    <div class="mt-3">
        <a href="{{ route('admin.staff.export', $user->id) }}" class="btn btn-success">CSV出力</a>
    </div>
</div>
@endsection
