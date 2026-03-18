<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminApplicationController extends Controller
{
    public function index()
    {
        $pendingAttendances = \App\Models\Attendance::where('status', 1)->with('user')->orderBy('date', 'desc')->get();
        $approvedAttendances = \App\Models\Attendance::where('status', 2)->with('user')->orderBy('date', 'desc')->get();
        return view('admin.application-list', compact('pendingAttendances', 'approvedAttendances'));
    }
}
