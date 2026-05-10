@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-index.css') }}">
@endsection

@section('content')
@include('components.header')
<div class="attendance-list">
    <div class="attendance-list__heading">
        <h1>勤怠一覧</h1>
    </div>
    <div class="attendance-list__month-nav">
        <a href="" class="attendance-list__month-link">
            前月
        </a>
        <div class="attendance-list__month">
            <img src="{{ asset('img/image.png')}}" alt="icon">
            <p>2023/06</p>
        </div>
        <a href="" class="attendance-list__month-link">
            翌月
        </a>
    </div>
    <table class="attendance-list__table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($days as $day)
                @php
                    $attendance = $attendances->get(
                        $day->format('Y-m-d')
                    );
                @endphp
            <tr>
                <td>{{ $day->format('m/d') }}({{ $day->isoFormat('ddd')}})</td>
                <td>{{ $attendance?->check_in?->format('H:i') }}</td>
                <td>{{ $attendance?->check_out?->format('H:i') }}</td>
                <td>1:00</td>
                <td>8:00</td>
                <td>
                    @if($attendance)
                    <a href="/attendance/detail/{{$attendance->id}}" class="attendance-list__table-detail">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection