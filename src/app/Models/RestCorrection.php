<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_id',
        'requested_start_time',
        'requested_end_time',
    ];

    protected $casts = [
        'requested_start_time' => 'datetime',
        'requested_end_time' => 'datetime',
    ];

    /**
     * この休憩修正が紐づく勤怠修正を取得
     */
    public function attendanceCorrection()
    {
        return $this->belongsTo(AttendanceCorrection::class);
    }
}
