<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 打刻画面を表示
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // 今日の勤怠レコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        // 勤怠状態を判定
        $status = $this->determineStatus($attendance);
        
        return view('attendance', compact('attendance', 'status'));
    }
    
    /**
     * 勤怠状態を判定
     */
    private function determineStatus($attendance)
    {
        if (!$attendance || !$attendance->clock_in) {
            return 'before_work'; // 勤務外
        }
        
        if ($attendance->clock_out) {
            return 'after_work'; // 退勤済
        }
        
        // 最新の休憩レコードを確認
        $latestBreak = $attendance->breaks()->latest()->first();
        
        if ($latestBreak && !$latestBreak->end) {
            return 'on_break'; // 休憩中
        }
        
        return 'working'; // 出勤中
    }
    
    /**
     * 出勤打刻
     */
    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now()->format('H:i:s');
        
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => $now,
            'notes' => null,
            'status' => 0, // 通常勤務
        ]);
        
        return redirect('/attendance');
    }
    
    /**
     * 休憩開始打刻
     */
    public function breakStart()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now()->format('H:i:s');
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        if ($attendance) {
            // 新しい休憩レコードを追加（endはnull）
            $attendance->breaks()->create([
                'start' => $now,
            ]);
        }
        
        return redirect('/attendance');
    }
    
    /**
     * 休憩終了打刻
     */
    public function breakEnd()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now()->format('H:i:s');
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        if ($attendance) {
            // 最新の休憩レコードの終了時刻を保存
            $latestBreak = $attendance->breaks()->whereNull('end')->latest()->first();
            if ($latestBreak) {
                $latestBreak->update([
                    'end' => $now,
                ]);
            }
        }
        
        return redirect('/attendance');
    }
    
    /**
     * 退勤打刻
     */
    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now()->format('H:i:s');
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        if ($attendance) {
            // 全休憩時間の合計を計算（start/endから算出）
            $breaks = $attendance->breaks()->whereNotNull('end')->get();
            $totalBreakSeconds = $breaks->reduce(function($carry, $break) {
                if ($break->start && $break->end) {
                    $start = strtotime($break->start);
                    $end = strtotime($break->end);
                    return $carry + ($end - $start);
                }
                return $carry;
            }, 0);

            $attendance->clock_out = $now;
            $attendance->save();
        }
        
        return redirect('/attendance');
    }
    
    /**
     * 勤怠一覧を表示
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        
        // 年月の取得（パラメータがなければ当月）
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        // 対象月の開始日と終了日
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
        
        // 該当月の勤怠データを取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('breaks')
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
        
        // 前月・翌月の計算
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();
        
        return view('attendance-list', compact('dates', 'year', 'month', 'prevMonth', 'nextMonth'));
    }
    
    /**
     * 勤怠詳細を表示
     */
    public function detail(Request $request, $id)
    {
        $user = Auth::user();
        
        // 新規作成の場合
        if ($id === 'new') {
            $date = $request->input('date');
            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date,
                ],
                [
                    'status' => 0,
                    'clock_in' => null,
                    'clock_out' => null,
                ]
            );
        } else {
            // 自分の勤怠データのみ取得
            $attendance = Attendance::where('id', $id)
                ->where('user_id', $user->id)
                ->with('breaks')
                ->firstOrFail();
        }
        
        // 編集可能判定
        $isEditable = $attendance->status !== 1;

        return view('attendance-detail', compact('attendance', 'isEditable'));
    }

    /**
     * 勤怠詳細を更新
     */
    public function store(UpdateAttendanceRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        
        // リクエストから現在表示している勤怠IDを取得
        $attendanceId = $request->input('attendance_id');
        
        // 自分の勤怠レコードを取得
        $attendance = Attendance::where('id', $attendanceId)
            ->where('user_id', $user->id)
            ->with('breaks')
            ->firstOrFail();
        
        // 日付を取得
        $date = $attendance->date->format('Y-m-d');
        
        // 出勤・退勤時刻を更新
        $attendance->clock_in = $date . ' ' . $validated['clock_in'];
        $attendance->clock_out = $date . ' ' . $validated['clock_out'];
        $attendance->notes = $validated['notes'];
        $attendance->status = 1; // 修正申請時は承認待ち状態にする
        
        // 既存の休憩レコードを削除
        $attendance->breaks()->delete();
        
        // 新しい休憩レコードを作成し、合計休憩時間を計算
        $totalBreakSeconds = 0;
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

        $attendance->save();
        
        return redirect()->route('attendance.detail', ['id' => $attendance->id]);
    }

    /**
     * 申請一覧を表示
     */
    public function applicationList()
    {
        $user = Auth::user();
        
        // 承認待ち（status=1）のデータを取得
        $pendingAttendances = Attendance::where('user_id', $user->id)
            ->where('status', 1)
            ->with(['user', 'breaks'])
            ->orderBy('date')
            ->get();
        
        // 承認済み（status=2）のデータを取得
        $approvedAttendances = Attendance::where('user_id', $user->id)
            ->where('status', 2)
            ->with(['user', 'breaks'])
            ->orderBy('date')
            ->get();
        
        return view('application-list', compact('pendingAttendances', 'approvedAttendances'));
    }
}
