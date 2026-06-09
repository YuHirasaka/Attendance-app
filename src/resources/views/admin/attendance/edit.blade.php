@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list-page.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance-edit.css') }}">
@endsection

@section('content')
@include('components.header')
@php
    $workDate = optional($attendance)->work_date ?? \Carbon\Carbon::parse($day);
    $name = optional($attendance)->user->name ?? $user->name;
@endphp
<div class="list-page">
    <x-page-heading>勤怠詳細</x-page-heading>

    @if($isReadonly)
        <table class="attendance-detail__table">
            <tr>
                <th>名前</th>
                <td class="attendance-detail__name">{{ $name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="attendance-detail__date">
                    <p>{{ $workDate->format('Y年') }}</p>
                    <p>{{ $workDate->format('n月j日') }}</p>
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
                    <p class="attendance-detail__reason-text">{{ $correction->reason }}</p>
                </td>
            </tr>
        </table>
        <p class="attendance-detail__notice">
            ※承認待ちのため修正はできません。
        </p>
    @else
        <form action="{{ route('admin.attendance.save') }}" method="post">
            @csrf
            <input type="hidden" name="user_id" value="{{ old('user_id', $user->id) }}">
            <input type="hidden" name="work_date" value="{{ old('work_date', $workDate->format('Y-m-d')) }}">
            @if($attendance)
            <input type="hidden" name="attendance_id" value="{{ old('attendance_id', $attendance->id) }}">
            @endif
            <table class="attendance-detail__table">
                <tr>
                    <th>名前</th>
                    <td class="attendance-detail__name">{{ $name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td class="attendance-detail__date">
                        <p>{{ $workDate->format('Y年') }}</p>
                        <p>{{ $workDate->format('n月j日') }}</p>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td class="attendance-detail__td">
                        <div class="attendance-detail__row">
                            <input type="text" name="check_in" value="{{ old('check_in', optional($attendance)->check_in?->format('H:i')) }}" class="attendance-detail__time-input">
                            <span class="attendance-detail__separator">〜</span>
                            <input type="text" name="check_out" value="{{ old('check_out', optional($attendance)->check_out?->format('H:i')) }}" class="attendance-detail__time-input">
                        </div>
                        <div class="attendance-detail__errors">
                            @if ($errors->has('check_in'))
                                {{ $errors->first('check_in') }}
                            @elseif ($errors->has('check_out'))
                                {{ $errors->first('check_out') }}
                            @endif
                        </div>
                    </td>
                </tr>
                @foreach (optional($attendance)->breaks ?? [] as $break)
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
                                name="breaks[{{ $break->id }}][break_start]"
                                value="{{ old('breaks.' . $break->id . '.break_start', $break->break_start?->format('H:i')) }}"
                                class="attendance-detail__time-input">
                            <span class="attendance-detail__separator">〜</span>
                            <input type="text"
                                name="breaks[{{ $break->id }}][break_end]"
                                value="{{ old('breaks.' . $break->id . '.break_end', $break->break_end?->format('H:i')) }}"
                                class="attendance-detail__time-input">
                        </div>
                        <div class="attendance-detail__errors">
                            @if ($errors->has('breaks.' .$break->id . '.break_start'))
                                {{ $errors->first('breaks.' .$break->id . '.break_start') }}
                            @elseif ($errors->has('breaks.' .$break->id . '.break_end'))
                                {{ $errors->first('breaks.' .$break->id . '.break_end') }}
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <th>休憩{{ (optional($attendance)->breaks ?? collect())->count() + 1 }}</th>
                    <td class="attendance-detail__td">
                        <div class="attendance-detail__row">
                            <input type="text"
                                name="breaks[new][break_start]"
                                value="{{ old('breaks.new.break_start') }}" class="attendance-detail__time-input">
                            <span class="attendance-detail__separator">〜</span>
                            <input type="text"
                                name="breaks[new][break_end]"
                                value="{{ old('breaks.new.break_end') }}"
                                class="attendance-detail__time-input">
                        </div>
                        <div class="attendance-detail__errors">
                            @if ($errors->has('breaks.new.break_start'))
                                {{ $errors->first('breaks.new.break_start') }}
                            @elseif ($errors->has('breaks.new.break_end'))
                                {{ $errors->first('breaks.new.break_end')}}
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td class="attendance-detail__td">
                        <textarea name="note" class="attendance-detail__reason">{{ old('note', optional($attendance)->note) }}</textarea>
                        <div class="attendance-detail__errors">
                            @error('note')
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
    @endif
</div>
@endsection
