<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/users",
 *     summary="List all users",
 *     description="Returns a list of all users. Requires authentication and 'view users' permission.",
 *     tags={"Users"},
 *     security={{"sanctum":{}}},

 *     @OA\Response(
 *         response=200,
 *         description="Users retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Hassan Musa"),
 *                     @OA\Property(property="email", type="string", example="hassan@example.com"),
 *                     @OA\Property(property="type", type="string", example="admin"),
 *                     @OA\Property(property="created_at", type="string", example="2025-07-23T20:15:00.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2025-07-23T20:15:00.000000Z")
 *                 )
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - Token not provided",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),

 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - User lacks permission",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
 *         )
 *     )
 * )
 */
    public function index()
    {
        $users = User::all();
        return response()->json([
            'message' => 'Users retrieved successfully',
            'data' => $users
        ], 200);
    }

    /**
 * @OA\Get(
 *     path="/api/users/{id}",
 *     summary="Get user by ID",
 *     description="Returns a specific user by their ID. Requires authentication and 'view users' permission.",
 *     tags={"Users"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer", example=1)
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="User retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User retrieved successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Hassan Musa"),
 *                 @OA\Property(property="email", type="string", example="hassan@example.com"),
 *                 @OA\Property(property="type", type="string", example="admin"),
 *                 @OA\Property(property="created_at", type="string", example="2025-07-23T20:15:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-07-23T20:15:00.000000Z")
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - Token not provided",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),

 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - User lacks permission",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
 *         )
 *     )
 * )
 */

    public function show(string $id)
    {
        $users = User::find($id);
        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => $users
        ], 200);
    }

/**
 * @OA\Patch(
 *     path="/api/users/{id}",
 *     summary="Update a user",
 *     description="Updates the specified user's name, email, or password. Requires authentication and 'edit users' permission.",
 *     tags={"Users"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user to update",
 *         @OA\Schema(type="integer", example=1)
 *     ),

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Name"),
 *             @OA\Property(property="email", type="string", format="email", example="updated@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="newpassword123")
 *         )
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="User updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User updated successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Updated Name"),
 *                 @OA\Property(property="email", type="string", example="updated@example.com"),
 *                 @OA\Property(property="type", type="string", example="admin"),
 *                 @OA\Property(property="created_at", type="string", example="2025-07-23T20:15:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-07-24T10:00:00.000000Z")
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - Token missing or invalid",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),

 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - User lacks permission",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
 *         )
 *     )
 * )
 */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if($request->has('name')){
            $user->name = $request->name;
        }
        if($request->has('email')){
            $user->email = $request->email;
        }
        if($request->has('password')){
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

/**
 * @OA\Delete(
 *     path="/api/users/{id}",
 *     summary="Delete a user",
 *     description="Deletes a specific user by ID. Requires authentication and 'delete users' permission.",
 *     tags={"Users"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user to delete",
 *         @OA\Schema(type="integer", example=1)
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="User deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User deleted successfully")
 *         )
 *     ),

 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User not found")
 *         )
 *     ),

 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - Token missing or invalid",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),

 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - User lacks permission",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
 *         )
 *     )
 * )
 */
    public function destroy(string $id)
    {
       $user = User::find($id);
       if(!$user){
        return response()->json([
            'message' => 'User not found'
        ], 404);
       }
       $user->delete();
       return response()->json([
        'message' => 'User deleted successfully'
       ], 200);
    }

    /**
 * @OA\Post(
 *     path="/api/users-search",
 *     summary="Search users",
 *     description="Searches users by name or email. Requires authentication and 'view users' permission.",
 *     tags={"Users"},
 *     security={{"sanctum":{}}},

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"query"},
 *             @OA\Property(property="query", type="string", example="hassan", description="Keyword to search by name or email")
 *         )
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Users retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Hassan Musa"),
 *                     @OA\Property(property="email", type="string", example="hassan@example.com"),
 *                     @OA\Property(property="type", type="string", example="admin"),
 *                     @OA\Property(property="created_at", type="string", example="2025-07-23T20:15:00.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2025-07-23T20:15:00.000000Z")
 *                 )
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - Token missing or invalid",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),

 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - User lacks permission",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
 *         )
 *     )
 * )
 */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();
        return response()->json([
            'message' => 'Users retrieved successfully',
            'data' => $users
        ], 200);
    }
}
