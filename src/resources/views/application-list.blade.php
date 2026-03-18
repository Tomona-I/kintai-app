@extends('layouts.auth-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/application-list.css') }}">
@endsection

@section('content')
<div class="application-list">
    <h1 class="application-list__title">申請一覧</h1>
    <div class="tab-navigation">
        <button class="tab-button tab-button--active" data-tab="pending">承認待ち</button>
        <button class="tab-button" data-tab="approved">承認済み</button>
    </div>

    <!-- 承認待ちの表 -->
    <table class="application-table" id="pending-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingAttendances as $attendance)
            <tr>
                <td>承認待ち</td>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->date->format('Y/m/d') }}</td>
                <td>{{ $attendance->notes ?? '' }}</td>
                <td>{{ $attendance->updated_at->format('Y/m/d') }}</td>
                <td><a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="6">承認待ちのデータがありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 承認済みの表 -->
    <table class="application-table" id="approved-table" style="display: none;">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse($approvedAttendances as $attendance)
            <tr>
                <td>承認済み</td>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->date->format('Y/m/d') }}</td>
                <td>{{ $attendance->notes ?? '' }}</td>
                <td>{{ $attendance->updated_at->format('Y/m/d') }}</td>
                <td><a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="6">承認済みのデータがありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const pendingTable = document.getElementById('pending-table');
    const approvedTable = document.getElementById('approved-table');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            tabButtons.forEach(btn => btn.classList.remove('tab-button--active'));
            this.classList.add('tab-button--active');
            const tabName = this.getAttribute('data-tab');
            if (tabName === 'pending') {
                pendingTable.style.display = 'table';
                approvedTable.style.display = 'none';
            } else if (tabName === 'approved') {
                pendingTable.style.display = 'none';
                approvedTable.style.display = 'table';
            }
        });
    });
});
</script>
@endsection
