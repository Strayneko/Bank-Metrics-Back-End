<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];


    public function accepted_bank()
    {
        return $this->hasMany(AcceptedBank::class, 'bank_id');
    }

    public function loan_reason()
    {
        return $this->hasMany(LoanReason::class, 'bank_id');
    }
}
