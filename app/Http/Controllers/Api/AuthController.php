<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/register/{type}",
 *     summary="Register a new user",
 *     description="Registers a new user of type 'admin' or 'customer'. Returns a user object and an authentication token.",
 *     tags={"Authentication"},
 *
 *     @OA\Parameter(
 *         name="type",
 *         in="path",
 *         description="User type to register. Either 'admin' or 'customer'.",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             enum={"admin", "customer"}
 *         )
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password"},
 *             @OA\Property(property="name", type="string", example="Hassan Musa"),
 *             @OA\Property(property="email", type="string", format="email", example="hassan@example.com"),
 *             @OA\Property(property="password", type="string", format="password", minLength=8, example="secret123")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="User registered successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User registered successfully"),
 *             @OA\Property(property="user", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Hassan Musa"),
 *                 @OA\Property(property="email", type="string", example="hassan@example.com"),
 *                 @OA\Property(property="type", type="string", example="admin"),
 *                 @OA\Property(property="created_at", type="string", example="2025-07-23T20:15:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-07-23T20:15:00.000000Z")
 *             ),
 *             @OA\Property(property="token", type="string", example="1|abcde12345...")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(
 *                     property="email",
 *                     type="array",
 *                     @OA\Items(type="string", example="The email has already been taken.")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function register(Request $request){
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);
        $type = $request->type;
        $data['password'] = Hash::make($data['password']);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'type' => "$type"
        ]);
        $token = $user->createToken(  $type .'_token')->plainTextToken;
        return response()->json([
            "message" => "User registered successfully",
            "user" => $user,
            "token" => $token
        ],201);
    }

/**
 * @OA\Post(
 *     path="/api/login/{type}",
 *     summary="Login a user",
 *     description="Logs in a user of the given type and returns a token.",
 *     tags={"Authentication"},
 *
 *     @OA\Parameter(
 *         name="type",
 *         in="path",
 *         required=true,
 *         description="User type (e.g., admin, customer)",
 *         @OA\Schema(
 *             type="string",
 *             enum={"admin", "customer"}
 *         )
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="hassan@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="secret12345")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User logged in successfully"),
 *             @OA\Property(property="user", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Hassan Musa"),
 *                 @OA\Property(property="email", type="string", example="hassan@example.com"),
 *                 @OA\Property(property="type", type="string", example="admin"),
 *                 @OA\Property(property="created_at", type="string", example="2025-07-23T20:15:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-07-23T20:15:00.000000Z")
 *             ),
 *             @OA\Property(property="token", type="string", example="1|abcdefg123456789")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Wrong password",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Wrong password")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User not found")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
    public function login(Request $request){
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);
        $type = $request->type;
        $user = User::where('email',$data['email'])->first();
        if(!$user){
            return response()->json([
                "message" => "User not found"
            ],404);
        }
        if(!Hash::check($data['password'],$user->password)){
            return response()->json([
                "message" => "Wrong password"
            ],401);
        }
        $token = $user->createToken( $type .'_token')->plainTextToken;
        return response()->json([
            "message" => "User logged in successfully",
            "user" => $user,
            "token" => $token
        ],200);
    }

    /**
 * @OA\Post(
 *     path="/api/logout",
 *     summary="Logout the authenticated user",
 *     description="Revokes the current access token for the authenticated user. Requires Bearer token.",
 *     tags={"Authentication"},
 *     security={{"sanctum":{}}},

 *     @OA\Response(
 *         response=200,
 *         description="Logged out successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User logged out successfully")
 *         )
 *     ),

 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated - Invalid or missing token",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "User logged out successfully"
        ],200);
    }
}
