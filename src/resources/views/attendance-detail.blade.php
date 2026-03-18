@extends('layouts.auth-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>
    
    @if($isEditable)
        <form action="{{ route('attendance.store') }}" method="POST" class="detail-form">
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
    @endif
    
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
                    @if($isEditable)
                    <div class="time-input-group">
                        <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}" class="time-input @error('clock_in') error @enderror">
                        <span class="time-separator">～</span>
                        <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}" class="time-input @error('clock_out') error @enderror">
                    </div>
                    @error('clock_in')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                    @error('clock_out')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                    @else
                    <div class="time-display-group">
                        <span class="time-text">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</span>
                        <span class="time-separator">～</span>
                        <span class="time-text">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</span>
                    </div>
                    @endif
                </td>
            </tr>
            @php
                $break1 = $attendance->breaks->get(0);
                $break2 = $attendance->breaks->get(1);
            @endphp
            <tr>
                <th>休憩</th>
                <td>
                    @if($isEditable)
                        <div class="time-input-group">
                            <input type="time" name="breaks[0][start]" value="{{ old('breaks.0.start', $break1 && $break1->start ? \Carbon\Carbon::parse($break1->start)->format('H:i') : '') }}" class="time-input @error('breaks.0.start') error @enderror">
                            <span class="time-separator">～</span>
                            <input type="time" name="breaks[0][end]" value="{{ old('breaks.0.end', $break1 && $break1->end ? \Carbon\Carbon::parse($break1->end)->format('H:i') : '') }}" class="time-input @error('breaks.0.end') error @enderror">
                        </div>
                        @error('breaks.0.start')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                        @error('breaks.0.end')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    @else
                        @if($break1 && $break1->start && $break1->end)
                            <div class="time-display-group">
                                <span class="time-text">{{ \Carbon\Carbon::parse($break1->start)->format('H:i') }}</span>
                                <span class="time-separator">～</span>
                                <span class="time-text">{{ \Carbon\Carbon::parse($break1->end)->format('H:i') }}</span>
                            </div>
                        @endif
                    @endif
                </td>
            </tr>
            <tr>
                <th>休憩2</th>
                <td>
                    @if($isEditable)
                        <div class="time-input-group">
                            <input type="time" name="breaks[1][start]" value="{{ old('breaks.1.start', $break2 && $break2->start ? \Carbon\Carbon::parse($break2->start)->format('H:i') : '') }}" class="time-input @error('breaks.1.start') error @enderror">
                            <span class="time-separator">～</span>
                            <input type="time" name="breaks[1][end]" value="{{ old('breaks.1.end', $break2 && $break2->end ? \Carbon\Carbon::parse($break2->end)->format('H:i') : '') }}" class="time-input @error('breaks.1.end') error @enderror">
                        </div>
                        @error('breaks.1.start')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                        @error('breaks.1.end')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    @else
                        @if($break2 && $break2->start && $break2->end)
                            <div class="time-display-group">
                                <span class="time-text">{{ \Carbon\Carbon::parse($break2->start)->format('H:i') }}</span>
                                <span class="time-separator">～</span>
                                <span class="time-text">{{ \Carbon\Carbon::parse($break2->end)->format('H:i') }}</span>
                            </div>
                        @endif
                    @endif
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    @if($isEditable)
                    <textarea name="notes" class="note-textarea @error('notes') error @enderror" rows="4">{{ old('notes', $attendance->notes) }}</textarea>
                    @error('notes')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                    @else
                    <div class="note-display">{{ $attendance->notes }}</div>
                    @endif
                </td>
            </tr>
        </table>
        
        <div class="form-actions">
            @if($isEditable)
            <button type="submit" class="submit-button">修正</button>
            @endif
            @if(!$isEditable)
            <p class="pending-message">*承認待ちのため修正できません。</p>
            @endif
        </div>
    
    @if($isEditable)
    </form>
    @endif
</div>
@endsection

