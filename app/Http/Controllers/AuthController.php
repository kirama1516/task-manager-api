<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(['token' => $token], 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'usermail' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginType = filter_var($fields['usermail'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $loginColumn = $loginType === 'email' ? 'email' : 'username';

        $user = User::where($loginColumn, $fields['usermail'])->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect Username or Email',
            ], 401);
        }

        if (! Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect Password',
            ], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout()
    {
        JWTAuth::logout();

        return response()->json(['message' => 'Logged out']);
    }
}
