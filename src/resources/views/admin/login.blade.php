@extends('layouts.app')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

@include('components.header')
<div class="auth">
    <div class="auth__heading">
        <div class="auth__title">
            <h1>管理者ログイン</h1>
        </div>
    </div>
    <div class="form">
        <form action="/login" class="auth-form" method="post" novalidate>
            @csrf
            <input type="hidden" name="guard" value="admin">
            <div class="auth-form__group">
                <label for="email" class="auth-form__label">メールアドレス</label>
                <div class="auth-form__content">
                    <input class="auth-form__input" type="email" name="email" value="{{ old('email') }}">
                    <div class="auth-form__error">
                        @error('email')
                        {{ $message}}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="auth-form__group">
                <label for="password" class="auth-form__label">パスワード</label>
                <div class="auth-form__content">
                    <input class="auth-form__input" type="password" name="password">
                    <div class="auth-form__error">
                        @error('password')
                        {{ $message}}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="auth-form__button">
                <button class="auth-form__submit" type="submit">管理者ログインする</button>
            </div>
        </form>
    </div>
</div>
@endsection