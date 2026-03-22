<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\UpdateAttendanceRequest;

class AdminAttendanceController extends Controller
{
    /**
     * 管理者用勤怠一覧
     */
    public function index(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        $day = $request->input('day', Carbon::now()->day);
        $current = Carbon::create($year, $month, $day);
        $prev = $current->copy()->subDay();
        $next = $current->copy()->addDay();
        $prevYear = $prev->year;
        $prevMonth = $prev->month;
        $prevDay = $prev->day;
        $nextYear = $next->year;
        $nextMonth = $next->month;
        $nextDay = $next->day;

        $targetDate = Carbon::create($year, $month, $day)->toDateString();

        // 選択した1日分の勤怠データを取得
        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('date', $targetDate)
            ->orderBy('date', 'desc')
            ->get();

        $users = User::all();

        return view('admin.attendance-list', compact('attendances', 'users', 'year', 'month', 'day', 'prevYear', 'prevMonth', 'prevDay', 'nextYear', 'nextMonth', 'nextDay'));
    }

    /**
     * 勤怠詳細表示
     */
    public function detail(Request $request, $id)
    {
        // 新規作成の場合
        if ($id === 'new') {
            $userId = $request->input('user_id');
            $date = $request->input('date');
            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => $userId,
                    'date' => $date,
                ],
                [
                    'status' => 0,
                    'clock_in' => null,
                    'clock_out' => null,
                ]
            );
        } else {
            $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);
        }
        $isEditable = true;
        return view('admin.attendance-detail', compact('attendance', 'isEditable'));
    }

    /**
     * 勤怠詳細更新
     */
    public function update(UpdateAttendanceRequest $request, $id)
    {
        $validated = $request->validated();
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);
        $date = $attendance->date->format('Y-m-d');
        // 出勤・退勤・備考を更新
        $attendance->clock_in = $date . ' ' . $validated['clock_in'];
        $attendance->clock_out = $date . ' ' . $validated['clock_out'];
        $attendance->notes = $validated['notes'];
        // 休憩レコードを再作成
        $attendance->breaks()->delete();
        if (!empty($validated['breaks'])) {
            foreach ($validated['breaks'] as $breakData) {
                if (!empty($breakData['start']) && !empty($breakData['end'])) {
                    $breakStart = $date . ' ' . $breakData['start'];
                    $breakEnd = $date . ' ' . $breakData['end'];
                    $attendance->breaks()->create([
                        'start' => $breakStart,
                        'end' => $breakEnd,
                    ]);
                }
            }
        }
        // ステータス変更（承認待ち）
        $attendance->status = 1;
        $attendance->save();
        // 勤怠一覧へリダイレクト時、修正した勤怠の年月日を渡す
        $year = $attendance->date->format('Y');
        $month = $attendance->date->format('m');
        $day = $attendance->date->format('d');
        return redirect()->route('admin.attendance.list', ['year' => $year, 'month' => $month, 'day' => $day]);
    }
}
