@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="form staff-list__form">
    <h1 class="content__heading staff-list__form-heading">スタッフ一覧</h1>

    {{-- テーブル --}}
    <table class="list-table">
        <tr class="list-table__row">
            <th class="list-table__label">名前</th>
            <th class="list-table__label">メールアドレス</th>
            <th class="list-table__label">月次勤怠</th>
        </tr >
        @forelse ($staffs as $staff)
            <tr class="list-table__row">
                <td class="list-table__data">{{ $staff->name }}</td>
                <td class="list-table__data">{{ $staff->email }}</td>
                <td class="list-table__data">
                    <a href="{{ route('admin.staff.attendance.show', ['id' => $staff->id]) }}" class="list-table__detail-btn">詳細</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">スタッフが登録されていません</td>
            </tr>
        @endforelse
    </table>

</div>
@endsection
