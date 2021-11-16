<?php

namespace App\Http\Controllers\API\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = FacadesValidator::make($request->all(),[
            'name' => 'required|string|max:50',
            'username' => 'required|string|max:50',
            // 'email' => 'required|string|email:rfc,dns|max:150|unique:users,email',
            'email' => 'required|string|email|max:150|unique:users,email',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'Error' => $validator->errors()
            ], 401);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password
        ]);

        // $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            // 'access_token' => $token,
            'message' => 'Registeration was successful',
            'token_type' => 'Bearer'
        ], 201);
    }

    public function login(Request $request)
    {
        try {

            $validator = FacadesValidator::make($request->all(),[
                'email' => 'required|string|email',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'Error' => $validator->errors()
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Credential invalid'
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error occur while authenticating',
                'error' => $th
            ], 500);
        }
    }

    public function logout(Request $request, User $user)
    {
        // auth()->user()->currentAccessToken()->delete();
        auth()->user()->tokens()->delete();
        // $user->tokens()->delete();
        // $request->user()->currentAccessToken()->delete();
        return response()->json([
                'message' => 'Logged out Successful',
            ], 201);
    }
}
