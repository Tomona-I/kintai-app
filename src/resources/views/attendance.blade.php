@extends('layouts.auth-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance-container">
        <div class="attendance__status">
            {{-- 状態: 勤務外、出勤中、休憩中、退勤済 --}}
            <span class="status-badge">勤務外</span>
            {{-- <span class="status-badge">出勤中</span> --}}
            {{-- <span class="status-badge">休憩中</span> --}}
            {{-- <span class="status-badge">退勤済</span> --}}
        </div>

        <div class="attendance__datetime">
            <p class="datetime__date">2026年2月1日(土)</p>
            <p class="datetime__time">09:00</p>
        </div>

        <div class="attendance__buttons">
            {{-- パターン1: 勤務外 --}}
            <button type="button" class="btn-attendance btn-attendance--primary">出勤</button>

            {{-- パターン2: 出勤中 --}}
            {{-- <div class="button-group">
                <button type="button" class="btn-attendance btn-attendance--primary">退勤</button>
                <button type="button" class="btn-attendance btn-attendance--secondary">休憩入</button>
            </div> --}}

            {{-- パターン3: 休憩中 --}}
            {{-- <button type="button" class="btn-attendance btn-attendance--secondary">休憩戻</button> --}}

            {{-- パターン4: 退勤済 --}}
            {{-- <p class="completion-message">お疲れ様でした。</p> --}}
        </div>
    </div>
@endsection
