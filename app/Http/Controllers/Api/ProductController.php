<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/products",
 *     summary="Get all products",
 *     description="Returns a list of all products with their images.",
 *     tags={"Products"},

 *     @OA\Response(
 *         response=200,
 *         description="Products retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Products retrieved successfully"),
 *             @OA\Property(
 *                 property="products",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="iPhone 15"),
 *                     @OA\Property(property="price", type="number", format="float", example=14999.99),
 *                     @OA\Property(property="description", type="string", example="Apple smartphone with A16 chip"),
 *                     @OA\Property(property="category_id", type="integer", example=2),
 *                     @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                     @OA\Property(
 *                         property="images",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="id", type="integer", example=10),
 *                             @OA\Property(property="url", type="string", example="https://example.com/images/iphone.jpg"),
 *                             @OA\Property(property="product_id", type="integer", example=1),
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
        $products = Product::with('images')->get();
        return response()->json([
            "message" => "Products retrieved successfully",
            "products" => $products
        ], 200);
    }


    /**
 * @OA\Post(
 *     path="/api/products",
 *     summary="Create a new product",
 *     description="Creates a new product with multiple images. Requires authentication and 'create products' permission.",
 *     tags={"Products"},
 *     security={{"sanctum":{}}},

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"name", "price", "stock", "category_id", "images"},
 *                 @OA\Property(property="name", type="string", example="iPhone 15"),
 *                 @OA\Property(property="price", type="number", format="float", example=14999.99),
 *                 @OA\Property(property="stock", type="integer", example=30),
 *                 @OA\Property(property="description", type="string", example="Apple smartphone with A16 chip"),
 *                 @OA\Property(property="category_id", type="integer", example=2),
 *                 @OA\Property(
 *                     property="images",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary")
 *                 )
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=201,
 *         description="Product created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Product created successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="iPhone 15"),
 *                 @OA\Property(property="slug", type="string", example="iphone-15"),
 *                 @OA\Property(property="price", type="number", format="float", example=14999.99),
 *                 @OA\Property(property="stock", type="integer", example=30),
 *                 @OA\Property(property="description", type="string", example="Apple smartphone with A16 chip"),
 *                 @OA\Property(property="category_id", type="integer", example=2),
 *                 @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                 @OA\Property(
 *                     property="images",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="id", type="integer", example=10),
 *                         @OA\Property(property="image", type="string", example="https://yourdomain.com/storage/products/iphone-15/img1.jpg"),
 *                         @OA\Property(property="product_id", type="integer", example=1),
 *                         @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                         @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z")
 *                     )
 *                 )
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'nullable',
            'category_id' => 'required|exists:categories,id',
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048'
        ]);
        $slug = Str::slug($request->name, '-');
        $slugExists = Product::where('slug', $slug)->exists();
        $slug = $slugExists ? $slug . '-' . uniqid() : $slug;

        $product = Product::create([
            "name" => $request->name,
            "slug" => $slug,
            "price" => $request->price,
            "stock" => $request->stock,
            "description" => $request->description,
            "category_id" => $request->category_id,
        ]);
        foreach ($request->file('images') as $img) {
            $filename = uniqid() . '_' . $img->getClientOriginalName();
            $path = $img->storeAs('products/' . $slug, $filename, 'public');
            Image::create([
                'image' => asset('storage/'.$path),
                'product_id' => $product->id
            ]);
        }
        return response()->json([
            "message" => "Product created successfully",
            "data" => $product->load('images')
        ], 201);
    }

/**
 * @OA\Get(
 *     path="/api/products/{id}",
 *     summary="Get a specific product by ID",
 *     description="Returns details of a specific product including its images.",
 *     tags={"Products"},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the product to retrieve",
 *         @OA\Schema(type="integer", example=1)
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Product retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Product retrieved successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="iPhone 15"),
 *                 @OA\Property(property="price", type="number", format="float", example=14999.99),
 *                 @OA\Property(property="description", type="string", example="Apple smartphone with A16 chip"),
 *                 @OA\Property(property="category_id", type="integer", example=2),
 *                 @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                 @OA\Property(
 *                     property="images",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="id", type="integer", example=10),
 *                         @OA\Property(property="url", type="string", example="https://example.com/images/iphone.jpg"),
 *                         @OA\Property(property="product_id", type="integer", example=1),
 *                         @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                         @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z")
 *                     )
 *                 )
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=404,
 *         description="Product not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Product not found")
 *         )
 *     )
 * )
 */
    public function show(string $id)
    {
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json([
            "message" => "Product retrieved successfully",
            "data" => $product
        ], 200);
    }

/**
 * @OA\Put(
 *     path="/api/products/{id}",
 *     summary="Update an existing product",
 *     description="Updates an existing product and its images. Requires authentication and 'edit products' permission.",
 *     tags={"Products"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the product to update",
 *         @OA\Schema(type="integer", example=1)
 *     ),

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
 *                 @OA\Property(property="price", type="number", format="float", example=15999.99),
 *                 @OA\Property(property="stock", type="integer", example=50),
 *                 @OA\Property(property="description", type="string", example="Updated description"),
 *                 @OA\Property(property="category_id", type="integer", example=2),
 *                 @OA\Property(
 *                     property="images",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary")
 *                 )
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Product updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="product updated successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
 *                 @OA\Property(property="slug", type="string", example="iphone-15-pro"),
 *                 @OA\Property(property="price", type="number", format="float", example=15999.99),
 *                 @OA\Property(property="stock", type="integer", example=50),
 *                 @OA\Property(property="description", type="string", example="Updated description"),
 *                 @OA\Property(property="category_id", type="integer", example=2),
 *                 @OA\Property(
 *                     property="images",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="id", type="integer", example=11),
 *                         @OA\Property(property="image", type="string", example="https://yourdomain.com/storage/products/iphone-15-pro/img1.jpg"),
 *                         @OA\Property(property="product_id", type="integer", example=1)
 *                     )
 *                 )
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=404,
 *         description="Product not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Product not found")
 *         )
 *     ),

 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The price must be a number.")
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
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
            'description' => 'nullable',
            'category_id' => 'sometimes|exists:categories,id',
            'images' => 'sometimes|array',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,svg|max:2048'
        ]);
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $slug = $product->slug;
        if ($request->has('name') && $request->name != $product->name) {
            $product->name = $request->name;
            $slug = Str::slug($request->name, '-');
            $slugExists = Product::where('slug', $slug)->exists();
            $slug = $slugExists ? $slug . '-' . uniqid() : $slug;
            $product->slug = $slug;
        }
        if($request->has('price')){
            $product->price = $request->price;
        }
        if($request->has('stock')){
            $product->stock = $request->stock;
        }
        if($request->has('description')){
            $product->description = $request->description;
        }
        if($request->has('category_id')){
            $product->category_id = $request->category_id;
        }
        if($request->hasFile('images')){
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img->image);
                $img->delete();
            }
            foreach ($request->file('images') as $img) {
                $filename = uniqid() . '_' . $img->getClientOriginalName();
                $path = $img->storeAs('products/' . $slug, $filename, 'public');
                Image::create([
                    'image' => asset('storage/'.$path),
                    'product_id' => $product->id
                ]);
            }
        }
        $product->save();
        return response()->json([
            'status' => 'success',
            'message' => 'product updated successfully',
            'data' => $product->load('images')
        ],200);
    }

/**
 * @OA\Delete(
 *     path="/api/products/{id}",
 *     summary="Delete a product",
 *     description="Deletes a product and all its associated images. Requires authentication and 'delete products' permission.",
 *     tags={"Products"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the product to delete",
 *         @OA\Schema(type="integer", example=1)
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Product deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Product deleted successfully")
 *         )
 *     ),

 *     @OA\Response(
 *         response=404,
 *         description="Product not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Product not found")
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
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }
        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->image);
            $img->delete();
        }
        $product->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully',
        ]);
    }


    /**
 * @OA\Post(
 *     path="/api/products-search",
 *     summary="Search products by name",
 *     description="Returns products that match the search query. Requires authentication.",
 *     tags={"Products"},
 *     security={{"sanctum":{}}},

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"query"},
 *             @OA\Property(property="query", type="string", example="iPhone")
 *         )
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Products retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Products retrieved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="iPhone 15"),
 *                     @OA\Property(property="price", type="number", format="float", example=14999.99),
 *                     @OA\Property(property="stock", type="integer", example=30),
 *                     @OA\Property(property="description", type="string", example="Apple smartphone with A16 chip"),
 *                     @OA\Property(property="category_id", type="integer", example=2),
 *                     @OA\Property(property="created_at", type="string", example="2025-07-23T21:00:00.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2025-07-23T21:00:00.000000Z")
 *                 )
 *             )
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
    public function search(Request $request)
    {
        $query = $request->input('query');
        $products = Product::where('name', 'like', '%' . $query . '%')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'data' => $products
        ],200);
    }
}
