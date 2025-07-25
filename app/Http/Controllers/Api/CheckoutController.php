<?php

namespace App\Http\Controllers\Api;

use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CheckoutController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/checkout",
 *     summary="Checkout and create an order",
 *     description="Creates an order from the user's cart. Requires authentication.",
 *     tags={"Checkout"},
 *     security={{"sanctum":{}}},

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={
 *                 "shipping_name", "shipping_address", "shipping_city",
 *                 "shipping_zipcode", "shipping_country", "shipping_phone"
 *             },
 *             @OA\Property(property="shipping_name", type="string", example="Ahmed Hassan"),
 *             @OA\Property(property="shipping_address", type="string", example="12 Main Street"),
 *             @OA\Property(property="shipping_city", type="string", example="Cairo"),
 *             @OA\Property(property="shipping_state", type="string", example="Giza"),
 *             @OA\Property(property="shipping_zipcode", type="string", example="12345"),
 *             @OA\Property(property="shipping_country", type="string", example="Egypt"),
 *             @OA\Property(property="shipping_phone", type="string", example="+201234567890"),
 *             @OA\Property(property="payment_method", type="string", example="cod"),
 *             @OA\Property(property="notes", type="string", example="Leave at the door")
 *         )
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Order placed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Order placed successfully"),
 *             @OA\Property(property="total", type="number", example=245.75),
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="order", type="object")
 *         )
 *     ),

 *     @OA\Response(
 *         response=400,
 *         description="Cart is empty or Product out of stock",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Cart is empty")
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
 *         response=500,
 *         description="Server error while placing order",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Failed to place order: DB error"),
 *             @OA\Property(property="status", type="boolean", example=false)
 *         )
 *     )
 * )
 */
    public function checkout(Request $request)
    {

        // Validate the request data
        $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_zipcode' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'payment_method' => 'nullable', // if null default to 'cod'
            'notes' => 'nullable|string',
        ]);
       $user = $request->user();
       $carts = Cart::with('product')->where('user_id',$user->id)->get();
       //check if cart is empty
       if($carts->isEmpty()){
           return response()->json([
               'message' => 'Cart is empty'
           ],400);
       }
       //check if product is out of stock
      $subtotal = 0;
        $orderItems = [];
        foreach($carts as $cart){
            $product = $cart->product;
            if($cart->product->stock < $cart->quantity){
                return response()->json([
                    'message' => "Product {$product->name} out of stock"
                ],400);
            }
            //calculate total price
           $itemSubTotal = round($product->price * $cart->quantity, 2);
           $subtotal += $itemSubTotal;
            //prepare order items
            $orderItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $cart->quantity,
                'price' => $product->price,
                'subtotal' => $itemSubTotal,
            ];
        }

            // tax and shipping cost
            $tax = round($subtotal * 0.08, 2); // assuming 8% tax
            $shippingCost = 5.00; // flat rate shipping cost
            $total = round($subtotal + $tax + $shippingCost, 2);
        //create order
        DB::beginTransaction();
           try {
            $order = new Order([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => OrderStatus::PENDING,
                'shipping_name' => $request->shipping_name,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_state' => $request->shipping_state,
                'shipping_zipcode' => $request->shipping_zipcode,
                'shipping_country' => $request->shipping_country,
                'shipping_phone' => $request->shipping_phone,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'payment_method' => $request->payment_method,
                'payment_status' => PaymentStatus::PENDING,
                'order_number' => Order::generateOrderNumber(),
                'notes' => $request->notes,
            ]);
            $user->orders()->save($order);
            //decrement product stock
            foreach($orderItems as $orderItem){
                $order->orderItems()->create([
                    'order_id' => $order->id,
                    'product_id' => $orderItem['product_id'],
                    'quantity' => $orderItem['quantity'],
                    'price' => $orderItem['price'],
                    'subtotal' => $orderItem['subtotal'],
                    'product_name' => $orderItem['product_name'],
                    'product_sku' => $orderItem['product_sku'],
                ]);
                Product::where('id',$orderItem['product_id'])
                ->decrement('stock',$orderItem['quantity']);
            }
            //clear cart
            Cart::where('user_id',$user->id)->each(function($cart){
                $cart->delete();
            });
            DB::commit();
            return response()->json([
                'message' => "Order placed successfully",
                'order' => $order->load('orderItems.product'),
                'total' => $total,
                'status' => true
            ], 200);
           } catch (\Exception $e) {
             DB::rollBack();
            return response()->json([
                'message' => "Failed  to place order : " . $e->getMessage(),
                'status' => false
            ], 500);
           }
    }

/**
 * @OA\Get(
 *     path="/api/orders",
 *     summary="Get order history for the authenticated user",
 *     description="Retrieves all orders placed by the logged-in user. Requires authentication.",
 *     tags={"Orders"},
 *     security={{"sanctum":{}}},

 *     @OA\Response(
 *         response=200,
 *         description="Order history retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Order history retrieved successfully"),
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(
 *                 property="orders",
 *                 type="array",
 *                 @OA\Items(type="object")
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
    public function orderHistory(Request $request)
    {
        $user = $request->user();
        $orders = $user->orders()->with('items')->get();

        return response()->json([
            'message' => 'Order history retrieved successfully',
            'orders' => $orders,
            'status' => true
        ]);
    }

/**
 * @OA\Get(
 *     path="/api/orders/{order}",
 *     summary="Get order details",
 *     description="Returns the details of a specific order for the authenticated user.",
 *     tags={"Orders"},
 *     security={{"sanctum":{}}},

 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="Order ID",
 *         @OA\Schema(type="integer", example=3)
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Order details retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Order details retrieved successfully"),
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="order", type="object")
 *         )
 *     ),

 *     @OA\Response(
 *         response=404,
 *         description="Order not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Order not found"),
 *             @OA\Property(property="status", type="boolean", example=false)
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
    public function orderDetails(Request $request, $id)
    {
        $user = $request->user();
        $order = $user->orders()->with('items')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found', 'status' => false], 404);
        }

        return response()->json([
            'message' => 'Order details retrieved successfully',
            'order' => $order,
            'status' => true
        ]);
    }

    /**
 * @OA\Get(
 *     path="/api/all-orders",
 *     summary="Get all orders (admin only)",
 *     description="Returns all orders in the system. Requires authentication and 'view orders' permission.",
 *     tags={"Orders"},
 *     security={{"sanctum":{}}},

 *     @OA\Response(
 *         response=200,
 *         description="All orders retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="All orders retrieved successfully"),
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(
 *                 property="orders",
 *                 type="array",
 *                 @OA\Items(type="object")
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

    public function getAllOrders()
    {
        $orders = Order::with('orderItems')->get();
        return response()->json([
            'message' => 'All orders retrieved successfully',
            'orders' => $orders,
            'status' => true
        ]);
    }
}
