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

    // relation to banks table
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
