<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bank extends Model
{
    use HasFactory;
    use SoftDeletes;
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Bank $bank) {
            $bank->name = Str::of($bank->name)->title()->trim();
        });
        static::updating(function (Bank $bank) {
            $bank->name = Str::of($bank->name)->title()->trim();
        });
    }
}
