<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    public $table = 'password_resets';
    public $timestamps = false;

    // protected $primarykey = null;
    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];
}
