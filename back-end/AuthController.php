<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // unique name, password min 4 char
        $request->validate([
            'username' => 'required|string|max:50|unique:users,Username',
            'password' => 'required|string|min:4'
        ]);

        $user = User::create([
            'Username' => $request->username,
            'PasswordHash' => Hash::make($request->password) 
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'UserID' => $user->UserID
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate(['username' => 'required', 'password' => 'required']);

        $user = User::where('Username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->PasswordHash)) {
            return response()->json(['error' => 'Invalid username or password'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'UserID' => $user->UserID,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function checkAuth(Request $request)
    {
        return response()->json([
            'loggedIn' => true, 
            'UserID' => $request->user()->UserID
        ]);
    }
}
