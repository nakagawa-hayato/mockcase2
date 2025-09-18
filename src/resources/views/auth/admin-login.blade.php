@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/auth.css')}}">
@endsection

@section('content')
<div class="auth-form">
    <h2 class="auth-form__heading content__heading">管理者ログイン</h2>
    <div class="auth-form__inner">
        <form class="auth-form__form" action="{{ route('admin.login') }}" method="post">
            @csrf
            <div class="auth-form__group">
                <label class="auth-form__label" for="email">メールアドレス</label>
                <input class="auth-form__input" type="email" name="email" id="email" value="{{ old('email') }}">
                <p class="auth-form__error-message">
                    @error('email')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="auth-form__group">
                <label class="auth-form__label" for="password">パスワード</label>
                <input class="auth-form__input" type="password" name="password" id="password">
                <p class="auth-form__error-message">
                    @error('password')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <p class="auth-form__error-message">
                @error('user')
                    {{ $message }}
                @enderror
            </p>
            <input class="auth-form__btn btn" type="submit" value="管理者ログインする">
        </form>
    </div>
</div>
@endsection('content')

