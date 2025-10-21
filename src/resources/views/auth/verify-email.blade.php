@extends('layouts.app')

@section('title', 'メール認証のご案内')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email__content">
    <div class="verify-email-form">
        @if (session('message'))
            <p class="verify-email-form__message">{{ session('message') }}</p>
        @else
            <p class="verify-email-form__message">
                ご登録ありがとうございます！<br>
                ご入力いただいたメールアドレスに認証リンクを送信しました。メールをご確認の上、認証を完了してください。
            </p>
        @endif

        @if (session('resent'))
            <p class="verify-email-form__success-message">
                新しい認証リンクをあなたのメールアドレスに送信しました。
            </p>
        @endif

        <div class="form__button">
            <a href="http://localhost:8025" target="_blank" class="form__button--auth-link">認証はこちらから</a>
        </div>

        <form class="form" method="POST" action="{{ route('verification.send') }}" novalidate>
            @csrf
            <button type="submit" class="login__button-submit">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection
