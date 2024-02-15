<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class RegisterVerify extends Model
{
    public $timestamps = false;

    protected $table = 'register_verify';

    protected $fillable = [
        'user_id',
        'verification_code',
        'expired_time',
    ];
}
