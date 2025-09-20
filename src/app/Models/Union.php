<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Union extends Model
{
    protected $fillable = [
        'name',
        'description',
        'logo',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function managers()
    {
        return $this->hasMany(UnionManager::class);
    }

    public function unionManagers()
    {
        return $this->hasMany(UnionManager::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function eventRegistrations()
    {
        return $this->hasManyThrough(EventRegistration::class, Event::class, 'union_id', 'event_id', 'id', 'id');
    }

    public function eventAttendances()
    {
        return $this->hasManyThrough(EventAttendance::class, Event::class, 'union_id', 'event_id', 'id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Lấy URL của logo
     */
    public function getLogoUrl(): string
    {
        if (!$this->logo) {
            // Trả về URL logo mặc định nếu không có logo
            return 'https://placehold.co/400x150/edeff5/4a5568?text=' . urlencode($this->name);
        }
        
        // Nếu logo là URL đầy đủ, trả về nguyên URL
        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }
        
        // Nếu logo là đường dẫn tương đối trong storage, trả về URL đầy đủ
        return asset('storage/' . $this->logo);
    }
}
