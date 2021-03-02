<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    public function setAgeAttribute(int $age)
    {
        $this->age = $age;
    }

    public function getAgeAttribute()
    {
        return 1;
    }

    public function keys()
    {
        return $this->hasMany(ProductKey::class);
    }

    public function softwares()
    {
        return $this->hasManyThrough(Software::class, ProductKey::class);
    }

    public function avatar()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }
}
