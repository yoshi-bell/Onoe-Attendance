<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'requester_id',
        'approver_id',
        'original_start_time',
        'original_end_time',
        'requested_start_time',
        'requested_end_time',
        'reason',
        'status'
    ];

    /**
     * この申請が紐づく勤怠記録を取得
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * この申請を行ったユーザーを取得
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * この申請を承認したユーザーを取得
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
