@extends('layouts.auth-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__title">勤怠一覧</h1>
    
    <div class="month-navigation">
        <a href="{{ route('attendance.list', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="month-navigation__link month-navigation__link--prev">←前月</a>
        <span class="month-navigation__current"><img src="{{ asset('img/calendar_logo.png') }}" alt="calendar" class="calendar-icon">{{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</span>
        <a href="{{ route('attendance.list', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="month-navigation__link month-navigation__link--next">翌月→</a>
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
            @foreach($dates as $dateData)
                @php
                    $date = $dateData['date'];
                    $attendance = $dateData['attendance'];
                @endphp
                <tr>
                    <td>{{ $date->format('m/d') }}({{ $date->isoFormat('ddd') }})</td>
                    <td>{{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                    <td>{{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                    <td>{{ $attendance ? $attendance->getTotalBreakTime() : '' }}</td>
                    <td>{{ $attendance ? $attendance->getWorkTime() : '' }}</td>
                    <td>
                        @if($attendance)
                            <a href="{{ url('/attendance/detail/' . $attendance->id) }}" class="detail-link">詳細</a>
                        @else
                            <a href="{{ route('attendance.detail', ['id' => 'new', 'date' => $date->format('Y-m-d')]) }}" class="detail-link">詳細</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
