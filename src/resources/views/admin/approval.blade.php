@extends('layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-approval.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>
    
    <form action="{{ url('/admin/stamp_correction_request/approve/' . $attendance->id) }}" method="POST" class="detail-form">
        @csrf
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td class="name-cell">{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <div class="date-display">
                        <span>{{ $attendance->date->format('Y年') }}</span>
                        <span>{{ $attendance->date->format('n月j日') }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <div class="time-display-group">
                        <span class="time-text">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</span>
                        <span class="time-separator">～</span>
                        <span class="time-text">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</span>
                    </div>
                </td>
            </tr>
            @foreach($attendance->breaks as $i => $break)
            <tr>
                <th>{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
                <td>
                    <div class="time-display-group">
                        <span class="time-text">
                            {{ $break->start ? \Carbon\Carbon::parse($break->start)->format('H:i') : '' }}
                        </span>
                        @if($break->start || $break->end)
                            <span class="time-separator">～</span>
                        @endif
                        <span class="time-text">
                            {{ $break->end ? \Carbon\Carbon::parse($break->end)->format('H:i') : '' }}
                        </span>
                    </div>
                </td>
            </tr>
            @endforeach
            <tr>
                <th>備考</th>
                <td>
                    <div class="note-display">{{ $attendance->notes }}</div>
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
