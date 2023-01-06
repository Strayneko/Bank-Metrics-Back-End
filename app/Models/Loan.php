<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // relation to users table
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // relation to accepted_banks table
    public function accepted_bank()
    {
        return $this->hasMany(AcceptedBank::class, 'loan_id');
    }
}
