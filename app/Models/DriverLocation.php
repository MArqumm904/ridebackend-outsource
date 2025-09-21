<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverLocation extends Model
{
    public $timestamps = false; // we manage updated_at only

    protected $fillable = [
        'driver_id','lat','lng','updated_at'
    ];
}
