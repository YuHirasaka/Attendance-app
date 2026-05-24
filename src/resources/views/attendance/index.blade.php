@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list-page.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance-index.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="list-page">
    <x-page-heading>勤怠一覧</x-page-heading>
    <div class="attendance-list__month-nav">
        <a href="{{ route('attendance.index', ['month' => $prevMonth]) }}" class="attendance-list__month-link">
            前月
        </a>
        <div class="attendance-list__month">
            <img src="{{ asset('img/image.png')}}" alt="icon">
            <p>{{ $currentMonth->format('Y/m') }}</p>
        </div>
        <a href="{{ route('attendance.index', ['month' => $nextMonth]) }}" class="attendance-list__month-link">
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
                <td>{{ $attendance?->break_time }}</td>
                <td>{{ $attendance?->work_time }}</td>
                <td>
                    @if($attendance)
                        <x-detail-link :href="route('attendance.edit', $attendance->id)" />
                    @else
                        <x-detail-link :href="route('attendance.create', ['date' => $day->format('Y-m-d')])" />
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
