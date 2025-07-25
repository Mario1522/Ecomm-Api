<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/carts",
 *     summary="Get all items in the authenticated user's cart",
 *     description="Returns a list of cart items with product details and total price. Requires authentication.",
 *     tags={"Cart"},
 *     security={{"sanctum":{}}},

 *     @OA\Response(
 *         response=200,
 *         description="Cart items retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Cart items retrieved successfully"),
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="total", type="number", format="float", example=4500.00),
 *             @OA\Property(
 *                 property="Cart",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="product_id", type="integer", example=5),
 *                     @OA\Property(property="user_id", type="integer", example=3),
 *                     @OA\Property(property="quantity", type="integer", example=2),
 *                     @OA\Property(property="product", type="object",
 *                         @OA\Property(property="id", type="integer", example=5),
 *                         @OA\Property(property="name", type="string", example="iPhone 15"),
 *                         @OA\Property(property="price", type="number", example=15000.00)
 *                     )
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="items",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="name", type="string", example="iPhone 15")
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
    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $carts = Cart::where('user_id', $user_id)->with('product')->get();
        $total = $carts->sum(function($cart) {
            return $cart->product->price * $cart->quantity;
        });
        $items = [];
        foreach($carts as $cart){
            $product = $cart->product;
            $items[] = [
                'name' => $product->name,
            ];
        }
        return response()->json([
            'message' => 'Cart items retrieved successfully',
            'Cart' => $carts,
            'total' => $total,
            'status' => 'success',
            'items' => $items
        ], 200);
    }

/**
 * @OA\Post(
 *     path="/api/carts",
 *     summary="Add a product to cart",
 *     description="Adds a product to the authenticated user's cart, or updates quantity if it already exists. Requires authentication.",
 *     tags={"Cart"},
 *     security={{"sanctum":{}}},

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"product_id", "quantity"},
 *             @OA\Property(property="product_id", type="integer", example=5),
 *             @OA\Property(property="quantity", type="integer", example=2)
 *         )
 *     ),

 *     @OA\Response(
 *         response=201,
 *         description="Cart item added successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Cart item added successfully"),
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(
 *                 property="Cart",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=12),
 *                 @OA\Property(property="user_id", type="integer", example=3),
 *                 @OA\Property(property="product_id", type="integer", example=5),
 *                 @OA\Property(property="quantity", type="integer", example=2),
 *                 @OA\Property(property="product", type="object",
 *                     @OA\Property(property="id", type="integer", example=5),
 *                     @OA\Property(property="name", type="string", example="iPhone 15"),
 *                     @OA\Property(property="price", type="number", example=15000.00)
 *                 )
 *             )
 *         )
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Cart item updated successfully (if already exists)",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Cart item updated successfully"),
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="Cart", type="object")
 *         )
 *     ),

 *     @OA\Response(
 *         response=400,
 *         description="Not enough stock",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Not enough stock"),
 *             @OA\Property(property="status", type="string", example="error")
 *         )
 *     ),

 *     @OA\Response(
 *         response=422,
 *         description="Validation Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object")
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
    public function store(Request $request)
    {
        $user_id = $request->user()->id;
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);
        $cart = Cart::where('user_id', $user_id)->where('product_id', $request->product_id)->first();
        $product = Product::findOrFail($request->product_id);
        if($cart){
            $total = $cart->quantity + $request->quantity;
            if($total > $product->stock){
                return response()->json([
                    'message' => 'Not enough stock',
                    'status' => 'error'
                ], 400);
            }
            $cart->update([
                'quantity' => $cart->quantity + $request->quantity
            ]);
            return response()->json([
                'message' => 'Cart item updated successfully',
                'Cart' => $cart->load('product'),
                'status' => 'success'
            ], 200);
        }else{
            if($product->stock < $request->quantity){
                return response()->json([
                    'message' => 'Not enough stock',
                    'status' => 'error'
                ], 400);
            }
            $cart = Cart::create([
                'user_id' => $user_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
            return response()->json([
                'message' => 'Cart item added successfully',
                'Cart' => $cart->load('product'),
                'status' => 'success'
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

/**
 * @OA\Put(
 *     path="/api/carts/{id}",
 *     summary="Update quantity of a cart item",
 *     description="Updates the quantity of a specific item in the authenticated user's cart. Requires authentication.",
 *     tags={"Cart"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the cart item",
 *         @OA\Schema(type="integer", example=12)
 *     ),

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"quantity"},
 *             @OA\Property(property="quantity", type="integer", example=3)
 *         )
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Cart item updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Cart item updated successfully"),
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="Cart", type="object")
 *         )
 *     ),

 *     @OA\Response(
 *         response=400,
 *         description="Quantity exceeds available stock",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Quantity exceeds available stock"),
 *             @OA\Property(property="status", type="string", example="error")
 *         )
 *     ),

 *     @OA\Response(
 *         response=404,
 *         description="Cart item not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Cart item not found"),
 *             @OA\Property(property="status", type="string", example="error")
 *         )
 *     ),

 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object")
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
    public function update(Request $request, string $id)
    {
        $user_id = $request->user()->id;
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);
        $cart = Cart::where('id', $id)->where('user_id', $user_id)->first();
        if(!$cart){
            return response()->json([
                'message' => 'Cart item not found',
                'status' => 'error'
            ], 404);
        }
        $product = Product::find($cart->product_id);
        if ($request->quantity > $product->stock) {
            return response()->json([
                'message' => 'Quantity exceeds available stock',
                'status' => 'error'
            ], 400);
        }

        $cart->update([
            'quantity' => $request->quantity
        ]);
        return response()->json([
            'message' => 'Cart item updated successfully',
            'Cart' => $cart->load('product'),
            'status' => 'success'
        ], 200);
    }

/**
 * @OA\Delete(
 *     path="/api/carts/{id}",
 *     summary="Delete item from cart",
 *     description="Deletes a specific item from the authenticated user's cart. Requires authentication.",
 *     tags={"Cart"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the cart item",
 *         @OA\Schema(type="integer", example=12)
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Cart item deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Cart item deleted successfully"),
 *             @OA\Property(property="status", type="string", example="success")
 *         )
 *     ),

 *     @OA\Response(
 *         response=404,
 *         description="Cart item not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Cart item not found"),
 *             @OA\Property(property="status", type="string", example="error")
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
    public function destroy(Request $request, string $id)
    {
        $user_id = $request->user()->id;
        $cart = Cart::where('id', $id)->where('user_id', $user_id)->first();
        if(!$cart){
            return response()->json([
                'message' => 'Cart item not found',
                'status' => 'error'
            ], 404);
        }
        $cart->delete();
        return response()->json([
            'message' => 'Cart item deleted successfully',
            'status' => 'success'
        ], 200);
    }
}
