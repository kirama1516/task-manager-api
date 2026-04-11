<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Profile fetched successfully',
            'data' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'username'   => 'nullable|string|max:255',
            'email'     => 'nullable|email|unique:users,email,' . $user->id,
        ]);

        Log::info('Check', [
            'validator' => $validator,
            'user' => $user,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update other fields
        $user->fill($request->only([
            'name',
            'username', 
            'email', 
            'phone', 
        ]));

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }
}
