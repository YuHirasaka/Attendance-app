@extends('layouts.app')

@section('title', '出勤登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp.css')}}">
@endsection

@section('content')

@include('components.header')
<div class="attendance">
    <section class="attendance-card">
        <p class="attendance-card__status">勤務外</p>
        <p class="attendance-card__date">2023年6月1日(木)</p>
        <h1 class="attendance-card__time">08:00</h1>
        <form action="/attendance" method="post">
            @csrf
            <button class="btn attendance-card__button">出勤</button>
        </form>
    </section>
</div>

@endsection