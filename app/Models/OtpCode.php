<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;
    protected $table = 'otps';

    protected $fillable = [
        'user_id',
        'recipient',
        'type',
        'code',
        'expires_at',
        'is_used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Relation with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
