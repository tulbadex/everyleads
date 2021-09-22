<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = FacadesValidator::make($request->all(),[
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'Error validation' => $validator->errors()
            ], 404);
        }
        /* $data = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:8'
        ]); */

        $user = User::create([
            'name' => $validator['name'],
            'email' => $validator['email'],
            'password' => Hash::make($validator['password'])
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]), 201;
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credential = (['email', 'password']);

            if (!Auth::attempt($request->only($credential))) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Unauthorized'
                ]);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            /* if (! Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Error in Login');
            } */
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'message' => ['Credential invalid']
                ], 404);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status_code' => 201,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error occur while authenticating',
                'error' => $th
            ], 500);
        }
    }

    public function logout()
    {
        // auth()->user()->tokens()->delete();
        auth()->user()->currentAccessToken()->delete();
        return [
            'message' => 'Logged out'
        ];
    }
}
