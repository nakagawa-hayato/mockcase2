<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'clock_in_at', 'clock_out_at'];

    protected $casts = [
        'date'        => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at'=> 'datetime',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    // --- 日付系 ---
    public function getWeekdayJpAttribute(): string
    {
        $weekJp = ['日','月','火','水','木','金','土'];
        return $weekJp[$this->date->dayOfWeek];
    }

    // 年だけ（例: 2025年）
    public function getDateYearLabelAttribute(): string
    {
        return $this->date->format('Y年');
    }

    // 月日だけ（例: 9月22日）
    public function getDateMonthDayLabelAttribute(): string
    {
        return $this->date->format('n月j日');
    }

    // 一覧（日付列用: 06/01(金)）
    public function getDateLabelAttribute(): string
    {
        return $this->date->format('m/d') . '（' . $this->weekday_jp . '）';
    }

    // 詳細（日付表示用: 2023年6月1日）
    public function getDateFullLabelAttribute(): string
    {
        return $this->date->format('Y年n月j日') . '（' . $this->weekday_jp . '）';
    }

    // 勤務時間（休憩控除後）
    public function getWorkMinutesAttribute()
    {
        if (!$this->clock_in_at || !$this->clock_out_at) return null;

        $total = $this->clock_in_at->diffInMinutes($this->clock_out_at);
        return $total - $this->breakMinutes();
    }

    // 休憩合計（分）
    public function breakMinutes()
    {
        return $this->breakTimes->sum(function ($break) {
            if ($break->start_time && $break->end_time) {
                return $break->start_time->diffInMinutes($break->end_time);
            }
            return 0;
        });
    }

    // 勤務時間フォーマット（HH:MM）
    public function getWorkHmAttribute()
    {
        $minutes = $this->work_minutes;
        if ($minutes === null) return null;
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    // 休憩時間フォーマット（HH:MM）
    public function getBreakHmAttribute()
    {
        $minutes = $this->breakMinutes();
        return $minutes ? sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60) : null;
    }

    // 出勤・退勤フォーマット
    public function getClockInHmAttribute()
    {
        return $this->clock_in_at?->format('H:i') ?? null;
    }

    public function getClockOutHmAttribute()
    {
        return $this->clock_out_at?->format('H:i') ?? null;
    }

    // ✅ 状態を動的算出
    public function getStatusAttribute(): string
    {
        if (!$this->clock_in_at) return 'off';

        if ($this->clock_in_at && !$this->clock_out_at) {
            $lastBreak = $this->breakTimes()->latest()->first();
            if ($lastBreak && !$lastBreak->end_time) return 'on_break';
            return 'working';
        }

        if ($this->clock_out_at) return 'finished';

        return 'off';
    }
}
