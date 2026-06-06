@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list-page.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance-edit.css') }}">
@endsection

@section('content')
@include('components.header')
<div class="list-page">
    <x-page-heading>勤怠詳細</x-page-heading>
    <form action="{{ route('admin.correction.update',[
        'attendance_correct_request_id' => $correction->id]) }}" method="post">
        @csrf
        @method('PATCH')
        <table class="attendance-detail__table">
            <tr>
                <th>名前</th>
                <td class="attendance-detail__name">{{ $correction->attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="attendance-detail__date">
                    <p>{{ $correction->attendance->work_date->format('Y年') }}</p>
                    <p>{{ $correction->attendance->work_date->format('n月j日') }}</p>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td class="attendance-detail__td">
                    <div class="attendance-detail__row">
                        <p>{{ $correction->requested_check_in->format('H:i') }}</p>
                        <span class="attendance-detail__separator">〜</span>
                        <p>{{ $correction->requested_check_out->format('H:i') }}</p>
                    </div>
                </td>
            </tr>
            @foreach ($correction->breaks as $correctionBreak)
            <tr>
                <th>
                    @if ($loop->first)
                        休憩
                    @else
                        休憩{{ $loop->iteration }}
                    @endif
                </th>
                <td class="attendance-detail__td">
                    <div class="attendance-detail__row">
                        <p>{{ $correctionBreak->requested_break_start->format('H:i') }}</p>
                        <span class="attendance-detail__separator">〜</span>
                        <p>{{ $correctionBreak->requested_break_end->format('H:i') }}</p>
                    </div>
                </td>
            </tr>
            @endforeach
            <tr>
                <th>備考</th>
                <td class="attendance-detail__td">
                    <p class="attendance-detail__reason-text p-flex">{{ $correction->reason }}</p>
                </td>
            </tr>
        </table>
        <div class="attendance-detail__button">
            @if ($correction->status === 'approved')
                <button class="attendance-detail__submit--approved" type="button" disabled>承認済み</button>
            @else
                <button class="attendance-detail__submit" type="submit">承認</button>
            @endif
        </div>
    </form>
</div>
@endsection
