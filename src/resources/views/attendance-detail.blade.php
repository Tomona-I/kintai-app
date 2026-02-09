@extends('layouts.auth-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
{{-- 仮の変数設定（後でコントローラーから渡す） --}}
@php
    $isEditable = false; // 修正可能な場合はtrue、承認待ちの場合はfalse
@endphp

<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>
    
    @if($isEditable)
    <form action="#" method="POST" class="detail-form">
        @csrf
    @endif
    
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
                    @if($isEditable)
                    <div class="time-input-group">
                        <input type="time" name="start_time" value="09:00" class="time-input">
                        <span class="time-separator">～</span>
                        <input type="time" name="end_time" value="18:00" class="time-input">
                    </div>
                    @else
                    <div class="time-display-group">
                        <span class="time-text">09:00</span>
                        <span class="time-separator">～</span>
                        <span class="time-text">18:00</span>
                    </div>
                    @endif
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>
                    @if($isEditable)
                    <div class="time-input-group">
                        <input type="time" name="break1_start" value="12:00" class="time-input">
                        <span class="time-separator">～</span>
                        <input type="time" name="break1_end" value="13:00" class="time-input">
                    </div>
                    @else
                    <div class="time-display-group">
                        <span class="time-text">12:00</span>
                        <span class="time-separator">～</span>
                        <span class="time-text">13:00</span>
                    </div>
                    @endif
                </td>
            </tr>
            <tr>
                <th>休憩2</th>
                <td>
                    @if($isEditable)
                    <div class="time-input-group">
                        <input type="time" name="break2_start" value="15:00" class="time-input">
                        <span class="time-separator">～</span>
                        <input type="time" name="break2_end" value="15:15" class="time-input">
                    </div>
                    @else
                    <div class="time-display-group">
                        <span class="time-text">15:00</span>
                        <span class="time-separator">～</span>
                        <span class="time-text">15:15</span>
                    </div>
                    @endif
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    @if($isEditable)
                    <textarea name="note" class="note-textarea" rows="4"></textarea>
                    @else
                    <div class="note-display"></div>
                    @endif
                </td>
            </tr>
        </table>
        
        <div class="form-actions">
            @if($isEditable)
            <button type="submit" class="submit-button">修正</button>
            @else
            <p class="pending-message">*承認待ちのため修正はできません。</p>
            @endif
        </div>
    
    @if($isEditable)
    </form>
    @endif
</div>
@endsection
