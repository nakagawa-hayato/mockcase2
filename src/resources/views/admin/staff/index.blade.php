@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="page-title">スタッフ一覧</h1>

    {{-- テーブル --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staffs as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>
                        <a href="{{ route('admin.staff.attendance.show', ['id' => $staff->id]) }}" class="btn-small">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">スタッフが登録されていません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
