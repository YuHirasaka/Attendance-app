@extends('layouts.app')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list-page.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance-index.css') }}">
<link rel="stylesheet" href="{{ asset('css/staff-attendance.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="list-page">
    <x-page-heading>{{ $user->name }}さんの勤怠</x-page-heading>
    <div class="attendance-list__month-nav">
        <a href="{{ route('admin.staff.attendance.index', [
            'id' => $user->id,
            'month' => $prevMonth,
            ]) }}" class="attendance-list__month-link">
            前月
        </a>
        <div class="attendance-list__month">
            <img src="{{ asset('img/image.png') }}" alt="icon">
            <p>{{ $currentMonth->format('Y/m') }}</p>
        </div>
        <a href="{{ route('admin.staff.attendance.index', [
            'id' => $user->id,
            'month' => $nextMonth,
            ]) }}" class="attendance-list__month-link">
            翌月
        </a>
    </div>
    <table class="attendance-list__table">
        <thead>
            <tr>
                <th class="attendance-list__table-head">日付</th>
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
                <td class="attendance-list__table-date">{{ $day->format('m/d')}}({{ $day->isoFormat('ddd')}})</td>
                <td>{{ $attendance?->check_in?->format('H:i') }}</td>
                <td>{{ $attendance?->check_out?->format('H:i') }}</td>
                <td>{{ $attendance?->break_time }}</td>
                <td>{{ $attendance?->work_time }}</td>
                <td>
                    @if($attendance)
                        <x-detail-link :href="route('admin.attendance.edit', $attendance->id)"/>
                    @else
                        <x-detail-link :href="route('admin.attendance.create', ['user_id' => $user->id, 'day' => $day->format('Y-m-d')])" />
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <form action="{{ route('admin.staff.attendance.export') }}" method="post">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <input type="hidden" name="month" value="{{ $currentMonth->format('Y-m') }}">
        <div class="staff-attendance__button">
            <button class="staff-attendance__submit" type="submit">CSV出力</button>
        </div>
    </form>
</div>
@endsection
