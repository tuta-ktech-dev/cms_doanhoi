<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    protected $fillable = [
        'union_id',
        'title',
        'description',
        'content',
        'image',
        'start_date',
        'end_date',
        'location',
        'max_participants',
        'activity_points',
        'status',
        'is_registration_open',
        'registration_deadline',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'activity_points' => 'decimal:2',
        'is_registration_open' => 'boolean',
    ];

    public function union()
    {
        return $this->belongsTo(Union::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function comments()
    {
        return $this->hasMany(EventComment::class);
    }

    public function attendance()
    {
        return $this->hasMany(EventAttendance::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeOpenForRegistration($query)
    {
        return $query->where('is_registration_open', true)
                    ->where(function ($q) {
                        $q->whereNull('registration_deadline')
                          ->orWhere('registration_deadline', '>', now());
                    });
    }

    public function scopeCompleted($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function getImageUrl(): string
    {
        if (!$this->image) {
            return 'https://placehold.co/800x400/edeff5/4a5568?text=' . urlencode($this->title);
        }
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }

    public function getRegistrationCount(): int
    {
        return $this->registrations()->where('status', 'approved')->count();
    }

    public function isRegistrationFull(): bool
    {
        if (!$this->max_participants) {
            return false;
        }
        return $this->getRegistrationCount() >= $this->max_participants;
    }

    public function getAttendanceCount(): int
    {
        return $this->attendance()->count();
    }

    public function getPresentCount(): int
    {
        return $this->attendance()->where('status', 'present')->count();
    }

    public function getAbsentCount(): int
    {
        return $this->attendance()->where('status', 'absent')->count();
    }

    public function getAttendanceRate(): float
    {
        $totalRegistrations = $this->getRegistrationCount();
        if ($totalRegistrations == 0) {
            return 0;
        }
        return round(($this->getAttendanceCount() / $totalRegistrations) * 100, 2);
    }

    public function getPresentRate(): float
    {
        $totalAttendance = $this->getAttendanceCount();
        if ($totalAttendance == 0) {
            return 0;
        }
        return round(($this->getPresentCount() / $totalAttendance) * 100, 2);
    }

    public function isCompleted(): bool
    {
        return $this->end_date < now();
    }

    public function isOngoing(): bool
    {
        return $this->start_date <= now() && $this->end_date >= now();
    }

    public function isUpcoming(): bool
    {
        return $this->start_date > now();
    }
}
