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
        <p id="date" class="attendance-card__date"></p>
        <h1 id="nowTime" class="attendance-card__time"></h1>
        <form action="/attendance" method="post">
            @csrf
            <button class="btn attendance-card__button">出勤</button>
        </form>
    </section>
</div>
<script>
'use strict'
    const dateEl = document.getElementById('date');
    const nowTimeEl = document.getElementById('nowTime');

    function showTime(){
        let today = new Date();
        let year = today.getFullYear();
        let month = today.getMonth() + 1;
        let day = today.getDate();
        let weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        let weekday = weekdays[today.getDay()];
        let hours = String(today.getHours()).padStart(2, '0');
        let minutes = String(today.getMinutes()).padStart(2, '0');
        let formatDate = `${year}年${month}月${day}日(${weekday})`;
        dateEl.textContent = formatDate;
        nowTimeEl.textContent = `${hours}:${minutes}`;
    }
    showTime();
    setInterval(showTime, 1000);
</script>
@endsection