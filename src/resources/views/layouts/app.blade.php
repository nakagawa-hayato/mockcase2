<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <div class="header__heading">
                <a href="/attendance" class="logo">
                    <img src="{{ asset('img/logo.svg') }}" alt="タイトル画像" class="img-logo-svg" />
                </a>
            </div>

            @yield('link')

            @auth
                <nav class="header__nav">
                    <ul class="header__nav-list">
                        @if (auth()->check() && auth()->user()->isAdmin())
                            {{-- 管理者専用メニュー --}}
                            <li class="header__nav-item">
                                <a href="{{ route('admin.attendance.index') }}" class="header__link">勤怠一覧</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="{{ route('admin.staff.index') }}" class="header__link">スタッフ一覧</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="{{ route('correction_request.list') }}" class="header__link">申請一覧</a>
                            </li>
                        @else
                            {{-- 一般ユーザー用 --}}
                            @if (session('afterClockOut'))
                                {{-- clockOut後のナビ --}}
                                <li class="header__nav-item">
                                    <a href="{{ route('attendance.index') }}" class="header__link">今月の出勤一覧</a>
                                </li>
                                <li class="header__nav-item">
                                    <a href="{{ route('attendance.correction.list') }}" class="header__link">申請一覧</a>
                                </li>
                            @elseif (request()->routeIs('attendance.detail') || request()->routeIs('attendance.show'))
                                @include('partials.nav-detail')
                            @else
                                {{-- 通常のナビ --}}
                                <li class="header__nav-item">
                                    <a href="{{ route('attendance.create') }}" class="header__link">勤怠</a>
                                </li>
                                <li class="header__nav-item">
                                    <a href="{{ route('attendance.index') }}" class="header__link">勤怠一覧</a>
                                </li>
                                <li class="header__nav-item">
                                    <a href="{{ route('attendance.correction.list') }}" class="header__link">申請</a>
                                </li>
                            @endif
                        @endif


                        <!-- 共通：ログアウト -->
                        <li class="header__nav-item">
                            <form action="{{ Auth::user()->role === 'admin' ? '/admin/logout' : '/logout' }}" method="POST">
                                @csrf
                                <button class="header-nav__button" type="submit">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            @endauth
        </header>

        <div class="content">
            @yield('content')
        </div>
    </div>
</body>

</html>


