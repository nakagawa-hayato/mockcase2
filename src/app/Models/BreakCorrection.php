<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'stamp_correction_request_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function stampCorrectionRequest()
    {
        return $this->belongsTo(StampCorrectionRequest::class);
    }

    public function getDurationMinutesAttribute(): int
    {
        if (!$this->start_time || !$this->end_time) return 0;
        return $this->end_time->diffInMinutes($this->start_time);
    }
}
