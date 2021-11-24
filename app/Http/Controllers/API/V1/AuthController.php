<?php

namespace App\Http\Controllers\API\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/v1/register",
     *   tags={"Register"},
     *   summary="User Register",
     *   operationId="register",
     *
     *  @OA\RequestBody(
     *      required=true,
     *      description="Create a user",
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *              required={"name", "username", "email", "password"},
     *              @OA\Property(property="name", type="string", format="text", example="tobais"),
     *              @OA\Property(property="username", type="string", format="text", example="tobais"),
     *              @OA\Property(property="email", type="string", format="email", example="example@email.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *          ),
     *     ),
     *   ),
     *
     *
     *   @OA\Response(
     *      response=201,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      ),
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *  )
     **/
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


    /**
     * @OA\Post(
     ** path="/api/v1/login",
     *   tags={"Login"},
     *   summary="Login",
     *   operationId="login",
     *
     *  @OA\RequestBody(
     *      required=true,
     *      description="pass user Credential",
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"email", "password"},
     *              @OA\Property(property="email", type="string", format="email", example="example@email.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *          ),
     *     ),
     *  ),
     *
     *  @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="access_token", type="object"),
     *        @OA\Property(property="token_type", type="object"),
     *     ),
     *  ),
     *
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Forbidden"
     *   )
     * )
     **/

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

    /**
     * @OA\Post(
     ** path="/api/v1/logout",
     *   tags={"Logout"},
     *   summary="Logout",
     *   operationId="logout",
     *   security={ {"bearerAuth": {} }},
     *
     *  @OA\Response(
     *     response=200,
     *     description="Success",
     *  ),
     *
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Forbidden"
     *   )
     * )
     **/
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
