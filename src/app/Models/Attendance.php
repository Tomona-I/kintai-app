<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'notes',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 休憩時間とのリレーション
     */
    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }
    
    /**
     * 休憩時間の合計を秒で取得
     */
    public function getTotalBreakSeconds()
    {
        return $this->breaks()
            ->whereNotNull('end')
            ->get()
            ->reduce(function($carry, $break) {
                if ($break->start && $break->end) {
                    $start = strtotime($break->start);
                    $end = strtotime($break->end);
                    return $carry + ($end - $start);
                }
                return $carry;
            }, 0);
    }
    
    /**
     * 休憩時間の合計を H:i 形式で取得
     */
    public function getTotalBreakTime()
    {
        $seconds = $this->getTotalBreakSeconds();
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }
    
    /**
     * 勤務時間を秒で取得
     */
    public function getWorkSeconds()
    {
        if (!$this->clock_in || !$this->clock_out) return 0;
        $clockIn = strtotime($this->clock_in);
        $clockOut = strtotime($this->clock_out);
        $breakSeconds = $this->getTotalBreakSeconds();
        return max(0, ($clockOut - $clockIn) - $breakSeconds);
    }
    
    /**
     * 勤務時間を H:i 形式で取得
     */
    public function getWorkTime()
    {
        $seconds = $this->getWorkSeconds();
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
