@extends('layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__title">2026年2月6日の勤怠</h1>
    
    <div class="date-navigation">
        <a href="#" class="date-navigation__link date-navigation__link--prev">←前日</a>
        <span class="date-navigation__current">2026/2/6</span>
        <a href="#" class="date-navigation__link date-navigation__link--next">翌日→</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th class="name-column">名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="name-column">山田 太郎</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td class="name-column">佐藤 花子</td>
                <td>08:30</td>
                <td>17:30</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td class="name-column">鈴木 一郎</td>
                <td>09:15</td>
                <td>18:45</td>
                <td>1:30</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td class="name-column">田中 次郎</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td class="name-column">高橋 美咲</td>
                <td>08:45</td>
                <td>17:45</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
