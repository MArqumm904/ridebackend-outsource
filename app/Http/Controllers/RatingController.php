<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Ride;
use App\Models\Rating;

class RatingController extends Controller
{
    public function rate(Request $request, Ride $ride)
    {
        $user = $request->user();
        if ($ride->rider_id !== $user->id) return response()->json(['message' => 'Forbidden'], 403);
        if ($ride->status !== 'completed') return response()->json(['message' => 'Ride not completed'], 400);

        $v = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1024',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $rating = Rating::create([
            'ride_id' => $ride->id,
            'rider_id' => $user->id,
            'driver_id' => $ride->driver_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['status' => true, 'rating' => $rating]);
    }
}
