@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css')}}">
@endsection

@section('content')
<div class="verify-email">
    <div class="verify-email__inner">
        <span class="verify-email__message">登録していただいたメールアドレスに認証メールを送付しました。</span><br>
        <span class="verify-email__message">メール認証を完了してください。</span>

        <div class="verify-email__page-link">
            <a href="https://mailtrap.io/inboxes" target="_blank" rel="noopener noreferrer">認証はこちらから</a>
        </div>

        <form class="resend" action="{{ route('verification.send') }}" method="POST">
            @csrf
            <button class="resend__btn" type="submit">認証メールを再送する</button>
        </form>
    </div>
</div>

@endsection('content')