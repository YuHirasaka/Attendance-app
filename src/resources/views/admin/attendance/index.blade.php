@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-index.css') }}">
@endsection

@section('content')
@include('components.header')
<div class="attendance-list">
    <div class="attendance-list__heading">
        <h1>{{ $currentDay->format('Y年n月j日') }}の勤怠</h1>
    </div>
    <div class="attendance-list__month-nav">
        <a href="{{ route('admin.attendance.index', ['day' => $prevDay]) }}" class="attendance-list__month-link">
            前日
        </a>
        <div class="attendance-list__month">
            <img src="{{ asset('img/image.png') }}" alt="icon">
            <p>{{ $currentDay->format('Y/m/d') }}</p>
        </div>
        <a href="{{ route('admin.attendance.index', ['day' => $nextDay]) }}" class="attendance-list__month-link">
            翌日
        </a>
    </div>
    <table class="attendance-list__table">
        <thead>
            <tr>
                <th class="attendance-list__table-head">名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                @php
                    $attendance = $attendances->get($user->id);
                @endphp
            <tr>
                <td class="attendance-list__table-date">{{ $user?->name }}</td>
                <td>{{ $attendance?->check_in?->format('H:i') }}</td>
                <td>{{ $attendance?->check_out?->format('H:i') }}</td>
                <td>{{ $attendance?->break_time }}</td>
                <td>{{ $attendance?->work_time }}</td>
                <td>
                    @if($attendance)
                        <a href="/admin/attendance/{{$attendance->id}}" class="attendance-list__table-detail">詳細</a>
                    @else
                        <a href="/admin/attendance/create?user_id={{ $user->id }}&day={{ $currentDay->format('Y-m-d') }}" class="attendance-list__table-detail">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection