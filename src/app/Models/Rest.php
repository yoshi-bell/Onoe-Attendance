<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'start_time',
        'end_time',
    ];

    /**
     * この休憩記録が紐づく勤怠記録を取得
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
