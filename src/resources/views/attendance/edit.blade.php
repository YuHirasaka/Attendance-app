@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-edit.css') }}">
@endsection

@section('content')
@include('components.header')
<div class="attendance-detail">
    <div class="attendance-detail__heading">
        <h1>勤怠詳細</h1>
    </div>
    <form action="{{ route('attendance_correction.store') }}" method="post">
        @csrf
        <input type="hidden" name="work_date" value="{{ old('work_date', $workDate->format('Y-m-d')) }}">
        @if($attendance)
        <input type="hidden" name="attendance_id" value="{{ old('attendance_id', $attendance->id) }}">
        @endif
        <table class="attendance-detail__table">
            <tr>
                <th>名前</th>
                <td class="attendance-detail__name">{{ $user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="attendance-detail__date">
                    <p>{{ $workDate->format('Y年')}}</p>
                    <p>{{ $workDate->format('n月j日')}}</p>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td class="attendance-detail__td">
                    <div class="attendance-detail__row">
                        <input type="text" name="requested_check_in" value="{{ old('requested_check_in', $checkIn?->format('H:i')) }}" class="attendance-detail__time-input">
                        <span class="attendance-detail__separator">〜</span>
                        <input type="text" name="requested_check_out" value="{{ old('requested_check_out', $checkOut?->format('H:i')) }}" class="attendance-detail__time-input">
                    </div>
                    <div class="attendance-detail__errors">
                        @if ($errors->has('requested_check_in'))
                            {{ $errors->first('requested_check_in') }}
                        @elseif ($errors->has('requested_check_out'))
                            {{ $errors->first('requested_check_out') }}
                        @endif
                    </div>
                </td>
            </tr>
            @foreach ($breaks as $break)
            @php
                $breakStart = $break->break_start ?? $break->requested_break_start ?? null;
                $breakEnd = $break->break_end ?? $break->requested_break_end ?? null;
            @endphp
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
                        <input type="text"
                            name="breaks[{{ $break->id }}][requested_break_start]"
                            value="{{ old('breaks.' . $break->id . '.requested_break_start', $breakStart?->format('H:i')) }}"
                            class="attendance-detail__time-input">
                        <span class="attendance-detail__separator">〜</span>
                        <input type="text"
                            name="breaks[{{ $break->id }}][requested_break_end]"
                            value="{{ old('breaks.' . $break->id . '.requested_break_end', $breakEnd?->format('H:i')) }}"
                            class="attendance-detail__time-input">
                    </div>
                    <div class="attendance-detail__errors">
                        @if ($errors->has('breaks.' .$break->id . '.requested_break_start'))
                            {{ $errors->first('breaks.' .$break->id . '.requested_break_start') }}
                        @elseif ($errors->has('breaks.' .$break->id . '.requested_break_end'))
                            {{ $errors->first('breaks.' .$break->id . '.requested_break_end') }}
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            <tr>
                <th>休憩{{ $breaks->count() + 1 }}</th>
                <td class="attendance-detail__td">
                    <div class="attendance-detail__row">
                        <input type="text"
                            name="breaks[new][requested_break_start]"
                            value="{{ old('breaks.new.requested_break_start') }}" class="attendance-detail__time-input">
                        <span class="attendance-detail__separator">〜</span>
                        <input type="text"
                            name="breaks[new][requested_break_end]"
                            value="{{ old('breaks.new.requested_break_end') }}"
                            class="attendance-detail__time-input">
                    </div>
                    <div class="attendance-detail__errors">
                        @if ($errors->has('breaks.new.requested_break_start'))
                            {{ $errors->first('breaks.new.requested_break_start') }}
                        @elseif ($errors->has('breaks.new.requested_break_end'))
                            {{ $errors->first('breaks.new.requested_break_end')}}
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td class="attendance-detail__td">
                    <textarea name="reason" class="attendance-detail__reason">{{ old('reason', $attendance?->note) }}</textarea>
                    <div class="attendance-detail__errors">
                        @error('reason')
                        {{ $message }}
                        @enderror
                    </div>
                </td>
            </tr>
        </table>
        <div class="attendance-detail__button">
            <button class="attendance-detail__submit" type="submit">修正</button>
        </div>
    </form>
</div>
@endsection