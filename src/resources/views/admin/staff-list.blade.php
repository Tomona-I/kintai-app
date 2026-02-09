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
            <tr>
                <td class="name-column">山田 太郎</td>
                <td class="email-column">yamada.taro@example.com</td>
                <td class="monthly-column"><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td class="name-column">佐藤 花子</td>
                <td class="email-column">sato.hanako@example.com</td>
                <td class="monthly-column"><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td class="name-column">鈴木 一郎</td>
                <td class="email-column">suzuki.ichiro@example.com</td>
                <td class="monthly-column"><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td class="name-column">田中 次郎</td>
                <td class="email-column">tanaka.jiro@example.com</td>
                <td class="monthly-column"><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td class="name-column">高橋 美咲</td>
                <td class="email-column">takahashi.misaki@example.com</td>
                <td class="monthly-column"><a href="#" class="detail-link">詳細</a></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
