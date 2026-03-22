@extends('layouts.auth-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance-container">
        <div class="attendance__status">
            @if($status === 'before_work')
                <span class="status-badge">勤務外</span>
            @elseif($status === 'working')
                <span class="status-badge">出勤中</span>
            @elseif($status === 'on_break')
                <span class="status-badge">休憩中</span>
            @elseif($status === 'after_work')
                <span class="status-badge">退勤済</span>
            @endif
        </div>

        <div class="attendance__datetime">
            <p class="datetime__date">{{ now()->isoFormat('YYYY年M月D日(ddd)') }}</p>
            <p class="datetime__time" id="current-time">{{ now()->format('H:i') }}</p>
        </div>

        <div class="attendance__buttons">
            @if($status === 'before_work')
                <form method="POST" action="{{ route('attendance.clock-in') }}">
                    @csrf
                    <button type="submit" class="btn-attendance btn-attendance--primary">出勤</button>
                </form>

            @elseif($status === 'working')
                <div class="button-group">
                    <form method="POST" action="{{ route('attendance.clock-out') }}">
                        @csrf
                        <button type="submit" class="btn-attendance btn-attendance--primary">退勤</button>
                    </form>
                    <form method="POST" action="{{ route('attendance.break-start') }}">
                        @csrf
                        <button type="submit" class="btn-attendance btn-attendance--secondary">休憩入</button>
                    </form>
                </div>

            @elseif($status === 'on_break')
                <form method="POST" action="{{ route('attendance.break-end') }}">
                    @csrf
                    <button type="submit" class="btn-attendance btn-attendance--secondary">休憩戻</button>
                </form>

            @elseif($status === 'after_work')
                <p class="completion-message">お疲れ様でした。</p>
            @endif
        </div>
    </div>

    <script>
        // 現在時刻を1秒ごとに更新
        setInterval(function() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('current-time').textContent = hours + ':' + minutes;
        }, 1000);
    </script>
@endsection
