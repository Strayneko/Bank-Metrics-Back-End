<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcceptedBank extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // protected $hidden = [
    //     // 'created_at',
    //     // 'updated_at',
    // ];

    // relation to bank model
    public function bank()
    {
        return  $this->belongsTo(Bank::class, 'bank_id', 'id');
    }
}
