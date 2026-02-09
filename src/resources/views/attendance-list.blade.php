@extends('layouts.auth-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__title">勤怠一覧</h1>
    
    <div class="month-navigation">
        <a href="#" class="month-navigation__link month-navigation__link--prev">←前月</a>
        <span class="month-navigation__current">2026/2</span>
        <a href="#" class="month-navigation__link month-navigation__link--next">翌月→</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>02/01(土)</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>02/02(日)</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>02/03(月)</td>
                <td>08:30</td>
                <td>17:30</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>02/04(火)</td>
                <td>09:15</td>
                <td>18:45</td>
                <td>1:30</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>02/05(水)</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>02/06(木)</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>02/07(金)</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>02/08(土)</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>02/09(日)</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>02/10(月)</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
