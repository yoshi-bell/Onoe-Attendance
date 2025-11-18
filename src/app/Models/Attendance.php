<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'work_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    /**
     * work_dateを日本語の曜日付きでフォーマットするアクセサ
     */
    public function getFormattedWorkDateAttribute()
    {
        return self::getFormattedDateWithDay($this->work_date);
    }

    /**
     * 日付を日本語の曜日付きでフォーマットする静的ヘルパーメソッド
     *
     * @param Carbon $date
     * @return string
     */
    public static function getFormattedDateWithDay(Carbon $date, $format = 'Y年m月d日')
    {
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        return $date->format($format) . '(' . $week[$date->dayOfWeek] . ')';
    }

    /**
     * この勤務の合計休憩時間を H:i 形式で取得するアクセサ
     */
    public function getTotalRestTimeAttribute()
    {
        $totalSeconds = 0;
        foreach ($this->rests as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $start = new \Carbon\Carbon($rest->start_time);
                $end = new \Carbon\Carbon($rest->end_time);
                $totalSeconds += $end->diffInSeconds($start);
            }
        }
        return $this->formatSecondsToHi($totalSeconds);
    }

    /**
     * この勤務の実働時間を H:i 形式で取得するアクセサ
     */
    public function getWorkTimeAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $start = new \Carbon\Carbon($this->start_time);
        $end = new \Carbon\Carbon($this->end_time);

        $totalWorkSeconds = $end->diffInSeconds($start);

        $totalRestSeconds = 0;
        foreach ($this->rests as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $restStart = new \Carbon\Carbon($rest->start_time);
                $restEnd = new \Carbon\Carbon($rest->end_time);
                $totalRestSeconds += $restEnd->diffInSeconds($restStart);
            }
        }

        $netWorkSeconds = $totalWorkSeconds - $totalRestSeconds;
        $netWorkSeconds = $netWorkSeconds > 0 ? $netWorkSeconds : 0;

        return $this->formatSecondsToHi($netWorkSeconds);
    }

    /**
     * 秒数を H:i 形式の文字列にフォーマットするヘルパーメソッド
     */
    private function formatSecondsToHi($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%d:%02d', $hours, $minutes);
    }
}
