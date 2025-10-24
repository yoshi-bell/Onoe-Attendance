<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Atte')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo" href="/">
                {{-- ロゴを画像に変更 --}}
                <img src="{{ asset('images/logo.svg') }}" alt="Atte">
            </a>

            {{-- ↓↓ ログインしている時だけナビゲーションを表示 ↓↓ --}}
            @auth
            <nav>
                <ul class="header-nav">
                    @if(Auth::check() && Auth::user()->is_admin)
                        {{-- 管理者用ヘッダー --}}
                        <li class="header-nav__item"><a class="header-nav__link" href="{{ route('admin.attendance.index') }}">勤怠一覧</a></li>
                        <li class="header-nav__item"><a class="header-nav__link" href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                        <li class="header-nav__item"><a class="header-nav__link" href="{{ route('admin.corrections.index') }}">申請一覧</a></li>
                        <li class="header-nav__item">
                            <form action="{{ route('admin.logout') }}" method="post" novalidate>
                                @csrf
                                <button class="header-nav__button">ログアウト</button>
                            </form>
                        </li>
                    @else
                        {{-- 一般ユーザー用ヘッダー --}}
                        <li class="header-nav__item"><a class="header-nav__link" href="/attendance">勤怠</a></li>
                        <li class="header-nav__item"><a class="header-nav__link" href="/attendance/list">勤怠一覧</a></li>
                        <li class="header-nav__item"><a class="header-nav__link" href="{{ route('corrections.index') }}">申請一覧</a></li>
                        <li class="header-nav__item">
                            <form action="{{ route('logout') }}" method="post" novalidate>
                                @csrf
                                <button class="header-nav__button">ログアウト</button>
                            </form>
                        </li>
                    @endif
                </ul>
            </nav>
            @endauth
            {{-- ↑↑ ここまでがログイン時のみ表示されるナビゲーション ↑↑ --}}
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
