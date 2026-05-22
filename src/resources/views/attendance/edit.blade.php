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
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        <table class="attendance-detail__table">
            <tr>
                <th>名前</th>
                <td class="attendance-detail__name">{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="attendance-detail__date">
                    <p>{{ $attendance->work_date->format('Y年')}}</p>
                    <p>{{ $attendance->work_date->format('n月j日')}}</p>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td class="attendance-detail__td">
                    <div class="attendance-detail__row">
                        @if($isReadonly)
                            <p>{{ $correction->requested_check_in->format('H:i') }}</p>
                            <span class="attendance-detail__separator">〜</span>
                            <p>{{ $correction->requested_check_out->format('H:i') }}</p>
                        @else
                            <input type="text" name="requested_check_in" value="{{ old('requested_check_in', $attendance->check_in?->format('H:i')) }}" class="attendance-detail__time-input">
                            <span class="attendance-detail__separator">〜</span>
                            <input type="text" name="requested_check_out" value="{{ old('requested_check_out', $attendance->check_out?->format('H:i')) }}" class="attendance-detail__time-input">
                        @endif
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
            @if($isReadonly)
                @foreach ($correction->breaks as $correctionBreak)
                <tr>
                    <th>
                        @if ($loop->first)
                            休憩
                        @else
                            休憩{{ $loop->iteration}}
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
            @else
                @foreach ($attendance->breaks as $break)
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
                                value="{{ old('breaks.' . $break->id . '.requested_break_start', $break->break_start?->format('H:i')) }}"
                                class="attendance-detail__time-input">
                            <span class="attendance-detail__separator">〜</span>
                            <input type="text"
                                name="breaks[{{ $break->id }}][requested_break_end]"
                                value="{{ old('breaks.' . $break->id . '.requested_break_end', $break->break_end?->format('H:i')) }}"
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
            @endif
            @if(!$isReadonly)
            <tr>
                <th>休憩{{ $attendance->breaks->count() + 1 }}</th>
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
            @endif
            <tr>
                <th>備考</th>
                <td class="attendance-detail__td">
                    @if($isReadonly)
                    <p class="attendance-detail__reason-text">{{ $correction->reason }}</p>
                    @else
                    <textarea name="reason" class="attendance-detail__reason"></textarea>
                    <div class="attendance-detail__errors">
                        @error('reason')
                        {{ $message }}
                        @enderror
                    </div>
                    @endif
                </td>
            </tr>
        </table>
        @if(!$isReadonly)
        <div class="attendance-detail__button">
            <button class="attendance-detail__submit" type="submit">修正</button>
        </div>
        @else
        <p class="attendance-detail__notice">
            ※承認待ちのため修正はできません。
        </p>
        @endif
    </form>
</div>
@endsection