@extends('layouts.app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

@include('components.header')
<div class="auth">
    <div class="auth__heading">
        <div class="auth__title">
            <h1>会員登録</h1>
        </div>
        <div class="form">
            <form action="/register" class="auth-form" method="post" novalidate>
                @csrf
                <div class="auth-form__group">
                    <label for="name" class="auth-form__label">名前</label>
                    <div class="auth-form__content">
                        <input class="auth-form__input" type="text" name="name" value="{{ old('name') }}">
                        <div class="auth-form__error">
                            @error('name')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="auth-form__group">
                    <label for="name" class="auth-form__label">メールアドレス</label>
                    <div class="auth-form__content">
                        <input class="auth-form__input" type="email" name="email" value="{{ old('email') }}">
                        <div class="auth-form__error">
                            @error('email')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="auth-form__group">
                    <label for="name" class="auth-form__label">パスワード</label>
                    <div class="auth-form__content">
                        <input class="auth-form__input" type="password" name="password">
                        <div class="auth-form__error">
                            @error('password')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="auth-form__group">
                    <label for="name" class="auth-form__label">パスワード確認</label>
                    <div class="auth-form__content">
                        <input class="auth-form__input" type="password" name="password_confirmation">
                        <div class="auth-form__error">
                            @error('password_confirmation')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="auth-form__button">
                    <button type="submit" class="auth-form__submit">登録する</button>
                </div>
            </form>
            <div class="auth-form__link">
                <a href="/login" class="auth-form__link-text">
                    ログインはこちら
                </a>
            </div>
        </div>
    </div>
</div>
@endsection