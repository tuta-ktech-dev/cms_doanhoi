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

    public function getImageUrl(): string
    {
        if (!$this->image) {
            return 'https://placehold.co/800x400/edeff5/4a5568?text=' . urlencode($this->title);
        }
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        return Storage::disk('public')->url($this->image);
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
}
