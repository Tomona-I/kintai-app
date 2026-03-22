<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;

class AdminApprovalController extends Controller
{
    public function show($attendanceId)
    {
        $attendance = Attendance::with('user', 'breaks')->findOrFail($attendanceId);
        $isApproved = $attendance->status == 2;
        return view('admin.approval', compact('attendance', 'isApproved'));
    }

    public function approve($attendanceId)
    {
        $attendance = Attendance::findOrFail($attendanceId);
        $attendance->status = 2;
        $attendance->save();
        return redirect()->route('admin.application-list')->with('success', '承認しました');
    }
}
