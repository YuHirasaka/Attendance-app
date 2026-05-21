@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/correction-index.css') }}">
@endsection

@section('content')
@include('components.header')
<div class="correction-list">
    <div class="correction-list__heading">
        <h1>申請一覧</h1>
    </div>
    <div class="correction-list__nav">
        <ul class="correction-list__nav-link">
            <li>
                <a href="/stamp_correction_request/list?page=pending"
                class="{{ request('page') === 'pending' ? 'is-active' : '' }}">承認待ち</a>
            </li>
            <li>
                <a href="/stamp_correction_request/list?page=approved"
                class="{{ request('page') === 'approved' ? 'is-active' : '' }}">承認済み</a>
            </li>
        </ul>
    </div>
    <table class="correction-list__table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendanceCorrections as $correction)
            <tr>
                <td>{{ $correction->status_label }}</td>
                <td>{{ $correction->attendance->user->name }}</td>
                <td>{{ $correction->attendance->work_date->format('Y/m/d') }}</td>
                <td class="correction-list__table-reason">{{ $correction->reason }}</td>
                <td>{{ $correction->created_at->format('Y/m/d')}}</td>
                <td class="correction-list__table-detail">
                    <a href="{{ route('correction.show', $correction->id) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection