@extends('layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-approval.css') }}">
@endsection

@section('content')
{{-- 仮の変数設定（後でコントローラーから渡す） --}}
@php
    $isApproved = false; // 承認済みの場合はtrue
@endphp

<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>
    
    <form action="#" method="POST" class="detail-form">
        @csrf
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td class="name-cell">山田 太郎</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <div class="date-display">
                        <span>2026年</span>
                        <span>2月5日</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <div class="time-display-group">
                        <span class="time-text">09:00</span>
                        <span class="time-separator">～</span>
                        <span class="time-text">18:00</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>
                    <div class="time-display-group">
                        <span class="time-text">12:00</span>
                        <span class="time-separator">～</span>
                        <span class="time-text">13:00</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>休憩2</th>
                <td>
                    <div class="time-display-group">
                        <span class="time-text">15:00</span>
                        <span class="time-separator">～</span>
                        <span class="time-text">15:15</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    <div class="note-display">電車遅延のため</div>
                </td>
            </tr>
        </table>
        
        <div class="form-actions">
            @if($isApproved)
            <button type="button" class="approval-button approval-button--approved">承認済み</button>
            @else
            <button type="submit" class="approval-button">承認</button>
            @endif
        </div>
    </form>
</div>
@endsection
