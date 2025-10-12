<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * ユーザーの勤怠記録を取得
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * ユーザーが行った修正申請を取得
     */
    public function requestedCorrections()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class, 'requester_id');
    }

    /**
     * ユーザーが承認した修正申請を取得
     */
    public function approvedCorrections()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class, 'approver_id');
    }
}
