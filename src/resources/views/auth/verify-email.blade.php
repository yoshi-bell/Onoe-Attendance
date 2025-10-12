@extends('layouts.app')

@section('title', 'メール認証のご案内')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email-container">
    <div class="verify-email-box">
        <p class="message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <div class="action-button">
            <a href="http://localhost:8025" target="_blank" class="verify-button">認証はこちらから</a>
        </div>

        <div class="resend-link-wrapper">
            <form class="resend-form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="resend-link">認証メールを再送する</button>
            </form>
        </div>
    </div>
</div>
@endsection
