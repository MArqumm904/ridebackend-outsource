<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\User;
use App\Models\DriverLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RideController extends Controller
{
    // Helper: Haversine distance in km
    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    // Helper: fare estimate (base + per km); tweakable via settings later if needed
    private function estimateFare(float $distanceKm): float
    {
        $base = 100; // PKR base fare
        $perKm = 50; // PKR per km
        return round($base + ($perKm * max(0, $distanceKm)), 2);
    }

    public function quote(Request $request)
    {
        $v = Validator::make($request->all(), [
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $distance = $this->haversine($request->pickup_lat, $request->pickup_lng, $request->dropoff_lat, $request->dropoff_lng);
        $fare = $this->estimateFare($distance);

        return response()->json([
            'status' => true,
            'distance_km' => round($distance, 3),
            'estimated_fare' => $fare,
        ]);
    }

    public function requestRide(Request $request)
    {
        $user = $request->user();
        $v = Validator::make($request->all(), [
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'pickup_address' => 'nullable|string',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',
            'dropoff_address' => 'nullable|string',
            'polyline' => 'nullable|string'
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $distance = $this->haversine($request->pickup_lat, $request->pickup_lng, $request->dropoff_lat, $request->dropoff_lng);
        $fare = $this->estimateFare($distance);

        $ride = Ride::create([
            'rider_id' => $user->id,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'pickup_address' => $request->pickup_address,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'dropoff_address' => $request->dropoff_address,
            'distance_km' => $distance,
            'estimated_fare' => $fare,
            'status' => 'requested',
            'polyline' => $request->polyline,
        ]);

        // Assign nearest online driver within ~5km
        $nearby = DriverLocation::query()
            ->join('users', 'users.id', '=', 'driver_locations.driver_id')
            ->where('users.role', 'driver')
            ->where('users.is_online', true)
            ->select('driver_locations.*', 'users.id as uid')
            ->get()
            ->map(function($dl) use ($request) {
                $dl->distance = $this->haversine($request->pickup_lat, $request->pickup_lng, (float)$dl->lat, (float)$dl->lng);
                return $dl;
            })
            ->sortBy('distance')
            ->first();

        if ($nearby && $nearby->distance <= 5) {
            $ride->driver_id = $nearby->uid;
            $ride->status = 'assigned';
            $ride->save();
        }

        return response()->json(['status' => true, 'ride' => $ride]);
    }

    public function show(Request $request, Ride $ride)
    {
        $user = $request->user();
        if ($ride->rider_id !== $user->id && $ride->driver_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return response()->json(['status' => true, 'ride' => $ride]);
    }

    public function list(Request $request)
    {
        $user = $request->user();
        $rides = Ride::query()
            ->where(function($q) use ($user) {
                $q->where('rider_id', $user->id)->orWhere('driver_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->paginate(20);
        return response()->json($rides);
    }

    public function cancel(Request $request, Ride $ride)
    {
        $user = $request->user();
        if ($ride->rider_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if (!in_array($ride->status, ['requested','assigned'])) {
            return response()->json(['message' => 'Cannot cancel at this stage'], 400);
        }
        $ride->status = 'canceled';
        $ride->canceled_at = now();
        $ride->canceled_by = 'rider';
        $ride->save();
        return response()->json(['status' => true, 'ride' => $ride]);
    }

    // Driver actions
    public function start(Request $request, Ride $ride)
    {
        $user = $request->user();
        if ($user->role !== 'driver' || $ride->driver_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if (!in_array($ride->status, ['assigned','driver_arriving'])) {
            return response()->json(['message' => 'Cannot start ride'], 400);
        }
        $ride->status = 'started';
        $ride->started_at = now();
        $ride->save();
        return response()->json(['status' => true, 'ride' => $ride]);
    }

    public function complete(Request $request, Ride $ride)
    {
        $user = $request->user();
        if ($user->role !== 'driver' || $ride->driver_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if ($ride->status !== 'started') {
            return response()->json(['message' => 'Cannot complete ride'], 400);
        }
        $ride->status = 'completed';
        $ride->completed_at = now();
        $ride->final_fare = $ride->estimated_fare; // simple: use estimate as final
        $ride->payment_status = 'unpaid';
        $ride->save();
        return response()->json(['status' => true, 'ride' => $ride]);
    }
}
