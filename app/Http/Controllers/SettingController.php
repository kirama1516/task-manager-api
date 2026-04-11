<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    // POST /settings/password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'  => 'required',
            'new_password'      => 'required|string|min:8|confirmed',
        ]);

        $user = User::find(Auth::user()->id);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([ 
                'success' => false,
                'errors' => $request->errors()
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
            'data' => $user
        ]);
    }

     // POST /settings/pin
    public function updatePin(Request $request)
    {
        $request->validate([
            'current_pin'  => 'required',
            'new_pin'      => 'required|string|min:8|confirmed',
        ]);

        $user = User::find(Auth::user()->id);

        if (!Hash::check($request->current_pin, $user->pin)) {
            return response()->json([
                'success' => false,
                'errors' => $request->errors()
            ], 422);
        }

        $user->pin = Hash::make($request->pin);
        $user->save();

        return response()->json([
             'success' => true,
            'message' => 'Pin updated successfully',
            'data' => $user
        ]);
    }
}
