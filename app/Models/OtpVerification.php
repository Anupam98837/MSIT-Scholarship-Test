<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'phone_number',
        'system_ip',
        'otp',
        'expires_at',
        'is_used',
        'attempt_count',
    ];

    protected $casts = [
        'expires_at'    => 'datetime',
        'is_used'       => 'boolean',
        'attempt_count' => 'integer',
    ];
}