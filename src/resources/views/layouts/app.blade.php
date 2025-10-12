<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Atte')</title>
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
                    @if(Auth::user()->is_admin)
                        {{-- 管理者用ヘッダー --}}
                        <li class="header-nav__item"><a class="header-nav__link" href="#">勤怠一覧</a></li>
                        <li class="header-nav__item"><a class="header-nav__link" href="#">スタッフ一覧</a></li>
                        <li class="header-nav__item"><a class="header-nav__link" href="#">申請一覧</a></li>
                    @else
                        {{-- 一般ユーザー用ヘッダー --}}
                        <li class="header-nav__item"><a class="header-nav__link" href="/attendance">勤怠</a></li>
                        <li class="header-nav__item"><a class="header-nav__link" href="/attendance/list">勤怠一覧</a></li>
                        <li class="header-nav__item"><a class="header-nav__link" href="#">申請</a></li>
                    @endif
                    <li class="header-nav__item">
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button class="header-nav__button">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
            @endauth
            {{-- ↑↑ ここまでがログイン時のみ表示されるナビゲーション ↑↑ --}}
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="footer__inner">
            <small>Atte, inc.</small>
        </div>
    </footer>
</body>
</html>
