<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\ContactMessage;

class SupportController extends Controller
{
    public function contact(Request $request)
    {
        $user = $request->user();
        
        // Debug: log the request data
        Log::info('Contact request data:', $request->all());
        
        $v = Validator::make($request->all(), [
            'name' => 'sometimes|nullable|string|max:150',
            'email' => 'sometimes|nullable|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        if ($v->fails()) {
            Log::error('Contact validation failed:', $v->errors()->toArray());
            return response()->json(['errors' => $v->errors()], 422);
        }

        $msg = ContactMessage::create([
            'user_id' => $user?->id,
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return response()->json(['status' => true, 'message' => 'Message received', 'ticket_id' => $msg->id]);
    }
}
