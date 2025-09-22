<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DriverLocation;
use App\Models\Ride;

class DriverController extends Controller
{
    public function setOnline(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'driver') return response()->json(['message' => 'Forbidden'], 403);
        $request->validate(['is_online' => 'required|boolean']);
        $user->is_online = $request->boolean('is_online');
        $user->save();
        return response()->json(['status' => true, 'is_online' => $user->is_online]);
    }

    public function updateLocation(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'driver') return response()->json(['message' => 'Forbidden'], 403);
        $v = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        DriverLocation::updateOrCreate(
            ['driver_id' => $user->id],
            ['lat' => $request->lat, 'lng' => $request->lng, 'updated_at' => now()]
        );
        return response()->json(['status' => true]);
    }

    public function rideLive(Request $request, Ride $ride)
    {
        $user = $request->user();
        if ($ride->rider_id !== $user->id && $ride->driver_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if (!$ride->driver_id) return response()->json(['status' => true, 'driver' => null]);
        $loc = DriverLocation::where('driver_id', $ride->driver_id)->first();
        // Simple ETA: distance from driver to pickup divided by avg speed (km/h)
        $etaMinutes = null;
        if ($loc && $ride->pickup_lat && $ride->pickup_lng) {
            $earthRadius = 6371; // km
            $dLat = deg2rad((float)$ride->pickup_lat - (float)$loc->lat);
            $dLon = deg2rad((float)$ride->pickup_lng - (float)$loc->lng);
            $a = sin($dLat/2) ** 2 + cos(deg2rad((float)$loc->lat)) * cos(deg2rad((float)$ride->pickup_lat)) * sin($dLon/2) ** 2;
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $distanceKm = $earthRadius * $c;
            $avgSpeedKmh = 20; // heuristic city speed
            $etaMinutes = (int) round(($distanceKm / max(1e-6, $avgSpeedKmh)) * 60);
        }

        return response()->json(['status' => true, 'driver' => [
            'id' => $ride->driver_id,
            'lat' => $loc?->lat,
            'lng' => $loc?->lng,
            'updated_at' => $loc?->updated_at,
            'eta_minutes' => $etaMinutes,
        ]]);
    }
}
