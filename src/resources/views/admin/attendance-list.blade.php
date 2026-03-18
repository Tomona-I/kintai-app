@extends('layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__title">{{ $year }}年{{ $month }}月{{ $day }}日の勤怠一覧</h1>
    
    <div class="date-navigation">
        <a href="{{ route('admin.attendance.list', ['year' => $prevYear, 'month' => $prevMonth, 'day' => $prevDay]) }}" class="date-navigation__link date-navigation__link--prev">←前日</a>
        <span class="date-navigation__current"><img src="{{ asset('img/caledar_logo.png') }}" alt="calendar" class="calendar-icon">{{ $year }}/{{ sprintf('%02d', $month) }}/{{ sprintf('%02d', $day) }}</span>
        <a href="{{ route('admin.attendance.list', ['year' => $nextYear, 'month' => $nextMonth, 'day' => $nextDay]) }}" class="date-navigation__link date-navigation__link--next">翌日→</a>
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
            @foreach($attendances as $attendance)
            <tr>
                <td class="name-column">{{ $attendance->user->name }}</td>
                <td>{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') : '' }}</td>
                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                <td>
                    @php
                        $breaks = $attendance->breaks->whereNotNull('end');
                        $totalBreakSeconds = $breaks->reduce(function($carry, $break) {
                            if ($break->start && $break->end) {
                                $start = strtotime($break->start);
                                $end = strtotime($break->end);
                                return $carry + ($end - $start);
                            }
                            return $carry;
                        }, 0);
                        $hours = floor($totalBreakSeconds / 3600);
                        $minutes = floor(($totalBreakSeconds % 3600) / 60);
                    @endphp
                    {{ sprintf('%d:%02d', $hours, $minutes) }}
                </td>
                <td>
                    @php
                        $workSeconds = 0;
                        if ($attendance->clock_in && $attendance->clock_out) {
                            $workSeconds = strtotime($attendance->clock_out) - strtotime($attendance->clock_in) - $totalBreakSeconds;
                        }
                        $workHours = floor($workSeconds / 3600);
                        $workMinutes = floor(($workSeconds % 3600) / 60);
                    @endphp
                    {{ sprintf('%d:%02d', $workHours, $workMinutes) }}
                </td>
                <td><a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection