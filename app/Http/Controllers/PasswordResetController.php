<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    // API: POST /api/forgot-password
    public function apiForgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['status' => true, 'message' => 'If your email exists, you will receive reset instructions.']);
        }

        $token = Str::random(60);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );

        Mail::to($request->email)->send(new PasswordResetMail($token, $request->email));

        return response()->json(['status' => true, 'message' => 'Reset link sent to your email.']);
    }

    // API: POST /api/reset-password
    public function apiReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        $reset = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired token'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) return response()->json(['status' => false, 'message' => 'User not found'], 404);

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['status' => true, 'message' => 'Password successfully reset']);
    }
}