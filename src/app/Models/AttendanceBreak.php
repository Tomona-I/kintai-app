<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    use HasFactory;
    
    protected $table = 'breaks';
    
    protected $fillable = [
        'attendance_id',
        'start',
        'end',
    ];
    
    /**
     * 勤怠レコードとのリレーション
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
