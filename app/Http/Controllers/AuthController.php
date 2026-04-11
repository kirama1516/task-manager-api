<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => true,
            'user' => $user,
            'token' => $token
        ], 201);

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usermail' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $loginType = filter_var($request->input('usermail'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($loginType, $request->input('usermail'))->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 401);
        }

        $validated = $validator->validated();

        if (! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect password',
            ], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => true,
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout()
    {
        JWTAuth::logout();

        return response()->json(['message' => 'Logged out']);
    }
}
