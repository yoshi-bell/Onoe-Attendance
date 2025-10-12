@extends('layouts.app')

@section('title', 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login-form">
    <h1 class="login-form__heading">ログイン</h2>
    <div class="login-form__inner">
        <form class="login-form__form" action="{{ route('login') }}" method="post">
            @csrf
            <div class="form-group">
                <input class="form-input" type="email" name="email" id="email" placeholder="メールアドレス" value="{{ old('email') }}">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <input class="form-input" type="password" name="password" id="password" placeholder="パスワード">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <button type="submit" class="submit-button">ログイン</button>
            </div>
        </form>
        <div class="register-link">
            <p class="register-link__text">アカウントをお持ちでない方はこちらから</p>
            <a href="{{ route('register') }}">会員登録</a>
        </div>
    </div>
</div>
@endsection
