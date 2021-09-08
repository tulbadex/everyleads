<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();
        if (!$user || Hash::check($request->password, $user->password)) {
            return response([
                'message' => ['Credential invalid']
            ], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response(
            [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ],
        201);
    }
}
