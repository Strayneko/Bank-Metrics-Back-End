<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];


    // relation to acceptedbank model
    public function accepted_bank()
    {
        return $this->hasMany(AcceptedBank::class, 'bank_id');
    }

    // relation to loanreason model
    public function loan_reason()
    {
        return $this->hasMany(LoanReason::class, 'bank_id');
    }
}
