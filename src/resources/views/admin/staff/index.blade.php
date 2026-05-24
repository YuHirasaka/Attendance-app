@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list-page.css') }}">
<link rel="stylesheet" href="{{ asset('css/staff-index.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="list-page">
    <x-page-heading>スタッフ一覧</x-page-heading>
    <table class="staff-list__table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <x-detail-link :href="route('admin.staff.attendance.index', $user->id)" />
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
