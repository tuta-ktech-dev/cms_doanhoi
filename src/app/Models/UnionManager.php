<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnionManager extends Model
{
    protected $fillable = [
        'user_id',
        'union_id',
        'position',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }
}
