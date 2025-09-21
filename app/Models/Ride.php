<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_id','driver_id',
        'pickup_lat','pickup_lng','pickup_address',
        'dropoff_lat','dropoff_lng','dropoff_address',
        'distance_km','estimated_fare','final_fare',
        'status','started_at','completed_at','canceled_at','canceled_by',
        'payment_method','payment_status','polyline'
    ];

    protected $casts = [
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'distance_km' => 'float',
        'estimated_fare' => 'float',
        'final_fare' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function rider() { return $this->belongsTo(User::class, 'rider_id'); }
    public function driver() { return $this->belongsTo(User::class, 'driver_id'); }
}
