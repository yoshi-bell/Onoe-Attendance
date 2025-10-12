<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
    ];

    /**
     * この勤怠記録を所有するユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この勤怠記録に紐づく休憩記録を取得
     */
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    /**
     * この勤怠記録に紐づく修正申請を取得
     */
    public function correctionRequest()
    {
        return $this->hasOne(AttendanceCorrectionRequest::class);
    }
}
