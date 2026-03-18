@extends('layouts.auth-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')
<div class="verify-container">
    <div class="verify-form">
        <p class="verify-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>
        <div class="verify-mailhog">
            <a href="http://localhost:8025" target="_blank" class="mailhog-button">認証はこちらから</a>
        </div>
        <div class="verify-actions">
            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="resend-button">認証メールを再送する</button>
            </form>
        </div>
    </div>
</div>
@endsection