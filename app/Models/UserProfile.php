<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    // relation to country model
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
