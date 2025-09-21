<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'user' => $request->user(),
        ]);
    }

    public function updateMe(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:150',
            'email' => 'sometimes|nullable|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:30|unique:users,phone,' . $user->id,
            'password' => 'sometimes|min:6',
            'cnic' => 'sometimes|nullable|string|max:50',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'profile_pic_url' => 'sometimes|nullable|url',
            'selfie' => 'sometimes|nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->fill($data);
        $user->save();

        return response()->json([
            'status' => true,
            'user' => $user->fresh(),
        ]);
    }

    public function updateGender(Request $request)
    {
        $request->validate([
            'gender' => 'required|in:male,female,other',
        ]);

        $user = $request->user();
        $user->gender = $request->gender;
        $user->save();

        return response()->json(['status' => true, 'message' => 'Gender updated', 'user' => $user]);
    }
}
