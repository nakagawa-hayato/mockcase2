<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'clock_in_at',
        'clock_out_at',
        'reason',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function breakCorrections()
    {
        return $this->hasMany(BreakCorrection::class);
    }

    // ✅ break_minutes の代わりに動的計算
    public function getBreakMinutesAttribute(): int
    {
        return $this->breakTime?->duration_minutes ?? 0;
    }
}
