<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'date_of_birth',
        'gender',
        'faculty',
        'class',
        'course',
        'activity_points',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'activity_points' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'user_id', 'user_id');
    }

    public function eventAttendances()
    {
        return $this->hasMany(EventAttendance::class, 'user_id', 'user_id');
    }
}
