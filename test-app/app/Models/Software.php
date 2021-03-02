<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    use HasFactory;

    public function secret()
    {
        return $this->hasOne(Secret::class);
    }

    public function keys()
    {
        return $this->hasMany(ProductKey::class);
    }

    public function owners()
    {
        return $this->hasManyThrough(Person::class, ProductKey::class);
    }

    public function images ()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
