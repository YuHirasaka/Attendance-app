@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
@include('components.header')
<div class="verify-email">
    <div class="verify-email__inner">
        <p class="verify-email__message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <div class="verify-email__button-wrapper">
            <a href="https://mailtrap.io/inboxes" target="_blank" class="verify-email__mailtrap-button">
                認証はこちらから
            </a>
        </div>

        <form action="/email/verification-notification" class="verify-email__resend-form" method="post">
            @csrf
            <button class="verify-email__resend-button">
                認証メールを再送する
            </button>
        </form>
    </div>
</div>
@endsection