<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // relation to loans table
    public function loan()
    {
        return $this->hasMany(Loan::class, 'bank_id');
    }
}
