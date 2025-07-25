<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/categories",
 *     summary="Get all top-level categories",
 *     description="Returns a list of categories that don't have a parent, along with their children.",
 *     tags={"Categories"},

 *     @OA\Response(
 *         response=200,
 *         description="Categories retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Categories retrieved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Electronics"),
 *                     @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *                     @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                     @OA\Property(
 *                         property="children",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="id", type="integer", example=2),
 *                             @OA\Property(property="name", type="string", example="Mobile Phones"),
 *                             @OA\Property(property="parent_id", type="integer", example=1),
 *                             @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                             @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z")
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return response()->json([
            "message" => "Categories retrieved successfully",
            "data" => $categories
        ],200);
    }

/**
 * @OA\Post(
 *     path="/api/categories",
 *     summary="Create a new category",
 *     description="Creates a new category with an optional parent. Requires authentication and 'create categories' permission.",
 *     tags={"Categories"},
 *     security={{"sanctum":{}}},

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Laptops", description="Name of the category"),
 *             @OA\Property(property="parent_id", type="integer", nullable=true, example=1, description="Optional parent category ID")
 *         )
 *     ),

 *     @OA\Response(
 *         response=201,
 *         description="Category created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Category created successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=5),
 *                 @OA\Property(property="name", type="string", example="Laptops"),
 *                 @OA\Property(property="slug", type="string", example="laptops"),
 *                 @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *                 @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z")
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The name field is required.")
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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id'
        ]);
        $slug = Str::slug($request->name , "-");
        $count = Category::where('slug' , $slug)->count();
        if($count > 0){
            $slug = $slug . '-' . ($count + 1);
        }
        $category = Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'parent_id' => $request->parent_id
        ]);
        return response()->json([
            "message" => "Category created successfully",
            "data" => $category
        ],201);
    }

/**
 * @OA\Get(
 *     path="/api/categories/{id}",
 *     summary="Get category by ID",
 *     description="Returns a specific category by its ID, including its children if any.",
 *     tags={"Categories"},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the category",
 *         @OA\Schema(type="integer", example=1)
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Category retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Category retrieved successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Electronics"),
 *                 @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *                 @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                 @OA\Property(
 *                     property="children",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="id", type="integer", example=2),
 *                         @OA\Property(property="name", type="string", example="Mobile Phones"),
 *                         @OA\Property(property="parent_id", type="integer", example=1),
 *                         @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                         @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z")
 *                     )
 *                 )
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Category not found")
 *         )
 *     )
 * )
 */

    public function show(string $id)
    {
        $category = Category::with('children')->find($id);
        if(!$category){
            return response()->json([
                "message" => "Category not found"
            ],404);
        }
        return response()->json([
            "message" => "Category retrieved successfully",
            "data" => $category
        ],200);
    }

/**
 * @OA\Patch(
 *     path="/api/categories/{id}",
 *     summary="Update a category",
 *     description="Updates a category by ID. Requires authentication and 'edit categories' permission.",
 *     tags={"Categories"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the category to update",
 *         @OA\Schema(type="integer", example=1)
 *     ),

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="New Category Name"),
 *             @OA\Property(property="parent_id", type="integer", nullable=true, example=2),
 *             @OA\Property(property="description", type="string", example="Updated description of the category"),
 *             @OA\Property(property="is_active", type="boolean", example=true)
 *         )
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Category updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Category updated successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="New Category Name"),
 *                 @OA\Property(property="slug", type="string", example="new-category-name"),
 *                 @OA\Property(property="parent_id", type="integer", nullable=true, example=2),
 *                 @OA\Property(property="description", type="string", example="Updated description of the category"),
 *                 @OA\Property(property="is_active", type="boolean", example=true),
 *                 @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-07-24T14:00:00.000000Z")
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),

 *     @OA\Response(
 *         response=403,
 *         description="Forbidden",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
 *         )
 *     ),

 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The name field is required.")
 *         )
 *     )
 * )
 */
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        if ($request->has('name') && $request->name != $category->name) {
            // Generate slug from name
            $slug = Str::slug($request->name, '-');
            // Check if slug is unique
            $count = Category::where('slug', $slug)
                ->where('id', '!=', $category->id)->count();
            if ($count > 0) {
                $slug = $slug . '-' . ($count + 1);
            }
            // Update the category name and slug
            $category->name = $request->name;
            $category->slug = $slug;
        }

        if (
            $request->has('parent_id')
            && $request->parent_id != $category->parent_id
        ) {
            $category->parent_id = $request->parent_id;
        }

        // Update the category with the validated data
        if ($request->has('description')) {
            $category->description = $request->description;
        }
        if ($request->has('is_active')) {
            $category->is_active = $request->is_active;
        }

        // Save the updated category
        $category->save();
        // Return the updated category as a JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

/**
 * @OA\Delete(
 *     path="/api/categories/{id}",
 *     summary="Delete a category",
 *     description="Deletes a category by ID and reassigns its children to its parent (or null). Requires authentication and 'delete categories' permission.",
 *     tags={"Categories"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the category to delete",
 *         @OA\Schema(type="integer", example=3)
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Category deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Category deleted successfully")
 *         )
 *     ),

 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Category not found")
 *         )
 *     ),

 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),

 *     @OA\Response(
 *         response=403,
 *         description="Forbidden",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
 *         )
 *     )
 * )
 */
    public function destroy(string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
        foreach ($category->children as $child) {
            $child->parent_id = $category->parent_id; // null
            $child->save();
        }
        $category->delete(); // tech
        // Return a success response
        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully',
        ]);
    }
/**
 * @OA\Get(
 *     path="/api/categories/{category}/products",
 *     summary="Get products of a category",
 *     description="Returns all products that belong to a specific category. Requires authentication.",
 *     tags={"Categories"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="category",
 *         in="path",
 *         required=true,
 *         description="ID of the category",
 *         @OA\Schema(type="integer", example=1)
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Products of Category retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Products of Category retrieved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=10),
 *                     @OA\Property(property="name", type="string", example="iPhone 15"),
 *                     @OA\Property(property="price", type="number", format="float", example=14999.99),
 *                     @OA\Property(property="category_id", type="integer", example=1),
 *                     @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z")
 *                 )
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Category] 999")
 *         )
 *     ),

 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function products(Category $category)
    {
        $category->load('products');
        // Return the category as a JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Products of Category retrieved successfully',
            'data' => $category->products
        ]);
    }
/**
 * @OA\Post(
 *     path="/api/categories-search",
 *     summary="Search categories by name",
 *     description="Searches for categories using a partial name match. Requires authentication.",
 *     tags={"Categories"},
 *     security={{"sanctum":{}}},

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Electro", description="Name or part of the name to search for")
 *         )
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Categories retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Categories retrieved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Electronics"),
 *                     @OA\Property(property="slug", type="string", example="electronics"),
 *                     @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *                     @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z")
 *                 )
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=422,
 *         description="Validation error (if name is missing)",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The name field is required.")
 *         )
 *     ),

 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function search(Request $request)
    {
        $categories = Category::where('name', 'like', '%' . $request->name . '%')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully',
            'data' => $categories
        ]);
    }
}
