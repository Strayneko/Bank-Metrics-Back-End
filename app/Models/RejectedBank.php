<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectedBank extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    // relation to bank model
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
