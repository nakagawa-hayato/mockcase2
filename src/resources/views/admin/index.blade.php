@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $date->format('Y年m月d日') }}の勤怠</h1>

    <div class="day-nav mb-3">
        <a href="{{ route('admin.attendance.index', ['date' => $prevDate]) }}">←前日</a>
        <span class="mx-3">{{ $date->format('Y/m/d') }}</span>
        <a href="{{ route('admin.attendance.index', ['date' => $nextDate]) }}">翌日→</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    {{-- 氏名 --}}
                    <td>{{ $attendance?->user->name }}</td>

                    {{-- 出勤 --}}
                    <td>{{ $attendance?->clock_in_hm }}</td>

                    {{-- 退勤 --}}
                    <td>{{ $attendance?->clock_out_hm }}</td>

                    {{-- 休憩 --}}
                    <td>{{ $attendance?->break_hm }}</td>

                    {{-- 勤務時間 --}}
                    <td>{{ $attendance?->work_hm }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.edit', $attendance->id) }}" class="btn btn-sm btn-info">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">この日の勤怠データはありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
