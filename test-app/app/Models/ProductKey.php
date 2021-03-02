<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductKey extends Model
{
    use HasFactory;

    public function owner()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }


    public function product()
    {
        return $this->belongsTo(Software::class, 'software_id');
    }

}
