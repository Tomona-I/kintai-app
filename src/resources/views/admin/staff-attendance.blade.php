@extends('layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-staff-attendance.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__title">{{ $user->name }}さんの勤怠</h1>
    
    <div class="month-navigation">
        <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'year' => $prevYear, 'month' => $prevMonth]) }}" class="month-navigation__link month-navigation__link--prev">←前月</a>
        <span class="month-navigation__current"><img src="{{ asset('img/calendar_logo.png') }}" alt="calendar" class="calendar-icon">{{ $year }}/{{ sprintf('%02d', $month) }}</span>
        <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'year' => $nextYear, 'month' => $nextMonth]) }}" class="month-navigation__link month-navigation__link--next">翌月→</a>
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
        @php
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        @endphp
        @foreach($dates as $dateData)
            @php
                $date = $dateData['date'];
                $attendance = $dateData['attendance'];
                
                // 休憩時間の計算
                $totalBreakMinutes = 0;
                if ($attendance) {
                    foreach ($attendance->breaks as $break) {
                        if ($break->start && $break->end) {
                            $breakStart = \Carbon\Carbon::parse($break->start);
                            $breakEnd = \Carbon\Carbon::parse($break->end);
                            $totalBreakMinutes += $breakStart->diffInMinutes($breakEnd);
                        }
                    }
                }
                $breakHours = floor($totalBreakMinutes / 60);
                $breakMinutes = $totalBreakMinutes % 60;
                $breakTime = $totalBreakMinutes > 0 ? sprintf('%d:%02d', $breakHours, $breakMinutes) : '';

                // 勤務時間の計算
                $workTime = '';
                if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                    $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
                    $clockOut = \Carbon\Carbon::parse($attendance->clock_out);
                    $totalMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes;
                    if ($totalMinutes > 0) {
                        $workHours = floor($totalMinutes / 60);
                        $workMinutes = $totalMinutes % 60;
                        $workTime = sprintf('%d:%02d', $workHours, $workMinutes);
                    }
                }
            @endphp
            <tr>
                <td>{{ $date->format('m/d') }}({{ $weekdays[$date->dayOfWeek] }})</td>
                <td>{{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                <td>{{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                <td>{{ $breakTime }}</td>
                <td>{{ $workTime }}</td>
                <td>
                    <a href="{{ route('admin.attendance.detail', ['id' => $attendance ? $attendance->id : 'new', 'user_id' => $user->id, 'date' => $date->format('Y-m-d')]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="csv-export-section">
        <a href="{{ route('admin.staff.attendance.csv', ['id' => $user->id, 'year' => $year, 'month' => $month]) }}" class="csv-export-button">CSV出力</a>
    </div>
</div>
@endsection
