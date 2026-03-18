<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AdminApprovalController extends Controller
{
    public function show($attendanceId)
    {
        $attendance = Attendance::with('user', 'breaks')->findOrFail($attendanceId);
        return view('admin.approval', compact('attendance'));
    }

    public function approve($attendanceId)
    {
        $attendance = Attendance::findOrFail($attendanceId);
        $attendance->status = 2;
        $attendance->save();
        return redirect()->route('admin.application-list')->with('success', '承認しました');
    }
}
