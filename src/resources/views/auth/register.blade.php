@extends('layouts.app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register-form">
    <h1 class="register-form__heading">会員登録</h1>
    <div class="register-form__inner">
        <form class="register-form__form" action="{{ route('register') }}" method="post">
            @csrf
            <div class="form-group">
                <input class="form-input" type="text" name="name" id="name" placeholder="名前" value="{{ old('name') }}">
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
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
                <input class="form-input" type="password" name="password_confirmation" id="password_confirmation" placeholder="確認用パスワード">
            </div>
            <div class="form-group">
                <button type="submit" class="submit-button">会員登録</button>
            </div>
        </form>
        <div class="login-link">
            <p class="login-link__text">アカウントをお持ちの方はこちらから</p>
            <a href="{{ route('login') }}">ログイン</a>
        </div>
    </div>
</div>
@endsection
