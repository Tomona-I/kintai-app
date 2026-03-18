@extends('layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-staff-list.css') }}">
@endsection

@section('content')
<div class="staff-list">
    <h1 class="staff-list__title">スタッフ一覧</h1>

    <table class="staff-table">
        <thead>
            <tr>
                <th class="name-column">名前</th>
                <th class="email-column">メールアドレス</th>
                <th class="monthly-column">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td class="name-column">{{ $user->name }}</td>
                <td class="email-column">{{ $user->email }}</td>
                <td class="monthly-column"><a href="{{ route('admin.staff.attendance', ['id' => $user->id]) }}" class="detail-link">詳細</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
