<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function accepted_bank()
    {
        return $this->hasMany(AcceptedBank::class, 'bank_id');
    }
}
