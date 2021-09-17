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
            ]);
        }
        /* $data = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:8'
        ]); */

        $data = $request->all();

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
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credential = (['email', 'password']);

            if (!Auth::attempt($credential)) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Unauthorized'
                ]);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            if (! Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Error in Login');
            }
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'message' => ['Credential invalid']
                ], 404);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status_code' => 200,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in login',
                'error' => $th
            ]);
        }
    }
}
