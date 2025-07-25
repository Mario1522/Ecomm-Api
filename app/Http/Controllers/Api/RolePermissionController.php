<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/roles",
 *     summary="Get all roles with their permissions",
 *     description="Returns all roles along with their assigned permissions. Requires authentication and 'view roles' permission.",
 *     tags={"Roles & Permissions"},
 *     security={{"sanctum": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Roles retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Roles retrieved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="admin"),
 *                     @OA\Property(property="guard_name", type="string", example="web"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *                     @OA\Property(
 *                         property="permissions",
 *                         type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="id", type="integer", example=1),
 *                             @OA\Property(property="name", type="string", example="view users"),
 *                             @OA\Property(property="guard_name", type="string", example="web"),
 *                             @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - You don't have the required permissions",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function getAllRoles(){
        $roles = Role::with('permissions')->get();
        return response()->json([
            'message' => 'Roles retrieved successfully',
            'data' => $roles
        ], 200);
    }

    /**
 * @OA\Get(
 *     path="/api/rolePermissions/{roleId}",
 *     summary="Get permissions of a specific role",
 *     description="Returns all permissions assigned to a specific role by role ID. Requires authentication and 'view roles' permission.",
 *     tags={"Roles & Permissions"},
 *     security={{"sanctum": {}}},
 *     @OA\Parameter(
 *         name="roleId",
 *         in="path",
 *         required=true,
 *         description="ID of the role",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permissions retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permissions retrieved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="edit products"),
 *                     @OA\Property(property="guard_name", type="string", example="web"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Role not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Role not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function getRolePermissions($roleId){
        $role = Role::find($roleId);
        if(!$role){
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }
        $permissions = $role->permissions;
        return response()->json([
            'message' => 'Permissions retrieved successfully',
            'data' => $permissions
        ], 200);
    }


    /**
 * @OA\Post(
 *     path="/api/assignRole",
 *     summary="Assign role to a user",
 *     description="Assigns a specific role to a user. Requires authentication and 'create roles' permission.",
 *     tags={"Roles & Permissions"},
 *     security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "role_id"},
 *             @OA\Property(property="user_id", type="integer", example=5),
 *             @OA\Property(property="role_id", type="integer", example=2)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Role assigned successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Role assigned successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User or Role not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function assignRoleToUser(Request $request){
        $user = User::find($request->user_id);
        if(!$user){
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        $role = Role::find($request->role_id);
        if(!$role){
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }
        $user->assignRole($role);
        return response()->json([
            'message' => 'Role assigned successfully'
        ], 200);
    }


    /**
 * @OA\Post(
 *     path="/api/createRole",
 *     summary="Create new role",
 *     description="Creates a new role. Requires authentication and 'create roles' permission.",
 *     tags={"Roles & Permissions"},
 *     security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="editor")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Role created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Role created successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=3),
 *                 @OA\Property(property="name", type="string", example="editor"),
 *                 @OA\Property(property="guard_name", type="string", example="web"),
 *                 @OA\Property(property="created_at", type="string", example="2025-07-25T12:34:56.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-07-25T12:34:56.000000Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The name field is required."),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="name", type="array",
 *                     @OA\Items(type="string", example="The name field is required.")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function createNewRole(Request $request){
        $role = Role::create(['name' => $request->name]);
        return response()->json([
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }


/**
 * @OA\Post(
 *     path="/api/deleteRoleFromUser",
 *     summary="Remove role from user",
 *     description="Removes a specific role from a user. Requires authentication and 'delete roles' permission.",
 *     tags={"Roles & Permissions"},
 *     security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "role_id"},
 *             @OA\Property(property="user_id", type="integer", example=5),
 *             @OA\Property(property="role_id", type="integer", example=3)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Role removed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Role removed successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User or Role not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User not found")
 *         )
 *     )
 * )
 */
    public function deleteRoleFromUser(Request $request){
        $user = User::find($request->user_id);
        if(!$user){
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        $role = Role::find($request->role_id);
        if(!$role){
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }
        $user->removeRole($role);
        return response()->json([
            'message' => 'Role removed successfully'
        ], 200);
    }

    /**
 * @OA\Get(
 *     path="/api/permissions",
 *     summary="Get all permissions",
 *     description="Retrieves a list of all permissions in the system. Requires authentication and 'view permissions' permission.",
 *     tags={"Roles & Permissions"},
 *     security={{"sanctum": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Permissions retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permissions retrieved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="edit products"),
 *                     @OA\Property(property="guard_name", type="string", example="web"),
 *                     @OA\Property(property="created_at", type="string", example="2024-01-01T00:00:00.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2024-01-01T00:00:00.000000Z")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function getAllPermissions(){
        $permissions = Permission::all();
        return response()->json([
            'message' => 'Permissions retrieved successfully',
            'data' => $permissions
        ], 200);
    }


    /**
 * @OA\Post(
 *     path="/api/assignPermissionToRole",
 *     summary="Assign permission to a role",
 *     description="Assigns a specific permission to a given role. Requires authentication and 'assign permissions' permission.",
 *     tags={"Roles & Permissions"},
 *     security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"role_id", "permission_id"},
 *             @OA\Property(property="role_id", type="integer", example=2),
 *             @OA\Property(property="permission_id", type="integer", example=5)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permission assigned successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permission assigned successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Role or permission not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Role not found")
 *         )
 *     )
 * )
 */
    public function assignPermissionToRole(Request $request){
        $role = Role::find($request->role_id);
        if(!$role){
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }
        $permission = Permission::find($request->permission_id);
        if(!$permission){
            return response()->json([
                'message' => 'Permission not found'
            ], 404);
        }
        $role->givePermissionTo($permission);
        return response()->json([
            'message' => 'Permission assigned successfully'
        ], 200);
    }

    /**
 * @OA\Post(
 *     path="/api/revokePermissionFromRole",
 *     summary="Revoke permission from a role",
 *     description="Removes a specific permission from a given role. Requires authentication and 'assign permissions' permission.",
 *     tags={"Roles & Permissions"},
 *     security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"role_id", "permission_id"},
 *             @OA\Property(property="role_id", type="integer", example=2),
 *             @OA\Property(property="permission_id", type="integer", example=5)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permission removed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permission removed successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Role or permission not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permission not found")
 *         )
 *     )
 * )
 */
    public function removePermissionFromRole(Request $request){
        $role = Role::find($request->role_id);
        if(!$role){
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }
        $permission = Permission::find($request->permission_id);
        if(!$permission){
            return response()->json([
                'message' => 'Permission not found'
            ], 404);
        }
        $role->revokePermissionTo($permission);
        return response()->json([
            'message' => 'Permission removed successfully'
        ], 200);
    }

/**
 * @OA\Post(
 *     path="/api/assignPermissionToUser",
 *     summary="Assign permission to a user",
 *     description="Assigns a specific permission to a given user. Requires authentication and 'assign permissions' permission.",
 *     tags={"Roles & Permissions"},
 *     security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "permission_id"},
 *             @OA\Property(property="user_id", type="integer", example=7),
 *             @OA\Property(property="permission_id", type="integer", example=4)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permission assigned successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permission assigned successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User or permission not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permission not found")
 *         )
 *     )
 * )
 */
    public function assignPermissionToUser(Request $request){
        $user = User::find($request->user_id);
        if(!$user){
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        $permission = Permission::find($request->permission_id);
        if(!$permission){
            return response()->json([
                'message' => 'Permission not found'
            ], 404);
        }
        $user->givePermissionTo($permission);
        return response()->json([
            'message' => 'Permission assigned successfully'
        ], 200);
    }


/**
 * @OA\Post(
 *     path="/api/revokePermissionFromUser",
 *     summary="Revoke permission from a user",
 *     description="Removes a specific permission from a user. Requires authentication and 'assign permissions' permission.",
 *     tags={"Roles & Permissions"},
 *     security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "permission_id"},
 *             @OA\Property(property="user_id", type="integer", example=7),
 *             @OA\Property(property="permission_id", type="integer", example=4)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permission removed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permission removed successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User or permission not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permission not found")
 *         )
 *     )
 * )
 */
    public function removePermissionFromUser(Request $request){
        $user = User::find($request->user_id);
        if(!$user){
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        $permission = Permission::find($request->permission_id);
        if(!$permission){
            return response()->json([
                'message' => 'Permission not found'
            ], 404);
        }
        $user->revokePermissionTo($permission);
        return response()->json([
            'message' => 'Permission removed successfully'
        ], 200);
    }
}
