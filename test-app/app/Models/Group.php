<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    public function parent()
    {
        return $this->belongsTo(self::class);
    }

    public function users()
    {
        return $this->morphedByMany(User::class, 'groupable');
    }

    public function people()
    {
        return $this->morphedByMany(Person::class, 'groupable');
    }
}
