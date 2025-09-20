<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAttendance extends Model
{
    use HasFactory;

    protected $table = 'event_attendance';

    protected $fillable = [
        'event_id',
        'user_id',
        'registered_by',
        'attended_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];

    /**
     * Get the event that this attendance belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who attended the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the student information through user.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'user_id', 'user_id');
    }

    /**
     * Get the user who registered this attendance.
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Get the status label in Vietnamese.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present' => 'Có mặt',
            'absent' => 'Vắng mặt',
            'late' => 'Đi muộn',
            'excused' => 'Có phép',
            default => $this->status,
        };
    }

    /**
     * Get the status color for badges.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'excused' => 'info',
            default => 'gray',
        };
    }
}