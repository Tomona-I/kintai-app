@extends('layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-approval.css') }}">
@endsection

@section('content')
{{-- 仮の変数設定（後でコントローラーから渡す） --}}
@php
    $isApproved = isset($attendance) && $attendance->status == 2;
@endphp

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
            @for ($i = 0; $i < 2; $i++)
            <tr>
                <th>{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
                <td>
                    <div class="time-display-group">
                        <span class="time-text">
                            {{ isset($attendance->breaks[$i]) && $attendance->breaks[$i]->start ? \Carbon\Carbon::parse($attendance->breaks[$i]->start)->format('H:i') : '' }}
                        </span>
                        @if(isset($attendance->breaks[$i]) && ($attendance->breaks[$i]->start || $attendance->breaks[$i]->end))
                            <span class="time-separator">～</span>
                        @endif
                        <span class="time-text">
                            {{ isset($attendance->breaks[$i]) && $attendance->breaks[$i]->end ? \Carbon\Carbon::parse($attendance->breaks[$i]->end)->format('H:i') : '' }}
                        </span>
                    </div>
                </td>
            </tr>
            @endfor
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
