<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::where('role', 0)->get();

        return view('admin.staff-list', compact('users'));
    }


    public function attendanceList(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $current = \Carbon\Carbon::create($year, $month, 1);
        $prev = $current->copy()->subMonth();
        $next = $current->copy()->addMonth();
        $prevYear = $prev->year;
        $prevMonth = $prev->month;
        $nextYear = $next->year;
        $nextMonth = $next->month;

        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth = $current->copy()->endOfMonth();

        // 該当月の勤怠データを取得
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('breaks')
            ->orderBy('date')
            ->get()
            ->keyBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // 月の全日付を生成
        $dates = [];
        $currentDate = $startOfMonth->copy();
        while ($currentDate <= $endOfMonth) {
            $dateKey = $currentDate->format('Y-m-d');
            $attendance = $attendances->get($dateKey);
            
            $dates[] = [
                'date' => $currentDate->copy(),
                'attendance' => $attendance,
            ];
            
            $currentDate->addDay();
        }

        return view('admin.staff-attendance', compact('user', 'dates', 'year', 'month', 'prevYear', 'prevMonth', 'nextYear', 'nextMonth'));
    }

    /**
     * スタッフ別勤怠一覧をCSV出力
     */
    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $current = \Carbon\Carbon::create($year, $month, 1);

        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth = $current->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('breaks')
            ->orderBy('date')
            ->get();

        // CSV用のヘッダーを設定
        $filename = $user->name . '_' . $year . '年' . sprintf('%02d', $month) . '月_勤怠一覧.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // CSVデータを生成
        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            // BOM付加（Excelで文字化け防止）
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // ヘッダー行
            fputcsv($file, ['日付', '曜日', '出勤', '退勤', '休憩時間', '勤務時間', '備考']);
            
            // データ行
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            foreach ($attendances as $attendance) {
                $date = \Carbon\Carbon::parse($attendance->date);
                $dateStr = $date->format('m/d');
                $weekday = $weekdays[$date->dayOfWeek];
                $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '';
                $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '';
                
                // 休憩時間の計算
                $totalBreakMinutes = 0;
                foreach ($attendance->breaks as $break) {
                    if ($break->start && $break->end) {
                        $breakStart = \Carbon\Carbon::parse($break->start);
                        $breakEnd = \Carbon\Carbon::parse($break->end);
                        $totalBreakMinutes += $breakStart->diffInMinutes($breakEnd);
                    }
                }
                $breakHours = floor($totalBreakMinutes / 60);
                $breakMinutes = $totalBreakMinutes % 60;
                $breakTime = $totalBreakMinutes > 0 ? sprintf('%d:%02d', $breakHours, $breakMinutes) : '';
                
                // 勤務時間の計算
                $workTime = '';
                if ($attendance->clock_in && $attendance->clock_out) {
                    $clockInTime = \Carbon\Carbon::parse($attendance->clock_in);
                    $clockOutTime = \Carbon\Carbon::parse($attendance->clock_out);
                    $totalMinutes = $clockInTime->diffInMinutes($clockOutTime) - $totalBreakMinutes;
                    if ($totalMinutes > 0) {
                        $workHours = floor($totalMinutes / 60);
                        $workMinutes = $totalMinutes % 60;
                        $workTime = sprintf('%d:%02d', $workHours, $workMinutes);
                    }
                }
                
                $notes = $attendance->notes ?? '';
                
                fputcsv($file, [$dateStr, $weekday, $clockIn, $clockOut, $breakTime, $workTime, $notes]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

