<?php

namespace App\Http\Controllers\Api;

use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymobPaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentGatewayInterface $paymentGateway;

    public function __construct(PaymentGatewayInterface $paymentGateway)
    {

        $this->paymentGateway = $paymentGateway;
    }


/**
 * @OA\Post(
 *     path="api/payment/process/{order}",
 *     operationId="processPayment",
 *     tags={"Payment"},
 *     summary="Process payment for a specific order",
 *     description="Initiate the payment process for a specific order using Paymob.",
 *     security={{"sanctum":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="Order ID to pay for",
 *         @OA\Schema(type="integer", example=15)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"amount_cents", "currency", "delivery_needed", "shipping_data"},
 *             @OA\Property(property="amount_cents", type="string", example="129601148"),
 *             @OA\Property(property="currency", type="string", example="EGP"),
 *             @OA\Property(property="delivery_needed", type="string", enum={"true", "false"}, example="false"),
 *             @OA\Property(
 *                 property="shipping_data",
 *                 type="object",
 *                 required={"first_name", "last_name", "phone_number", "email"},
 *                 @OA\Property(property="first_name", type="string", example="Test"),
 *                 @OA\Property(property="last_name", type="string", example="Account"),
 *                 @OA\Property(property="phone_number", type="string", example="01010101010"),
 *                 @OA\Property(property="email", type="string", format="email", example="test@account.com")
 *             ),
 *             @OA\Property(
 *                 property="items",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     required={"name", "amount_cents", "quantity"},
 *                     @OA\Property(property="name", type="string", example="ASC1525"),
 *                     @OA\Property(property="amount_cents", type="string", example="4000"),
 *                     @OA\Property(property="quantity", type="string", example="1"),
 *                     @OA\Property(property="description", type="string", example="Smart Watch", nullable=true)
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Payment link generated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="url", type="string", example="https://accept.paymob.com/payment-request-url")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request or order not payable",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Order Can Not Be Paid")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized to pay for this order",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthorized To Pay This Product")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error from Paymob",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Failed  to place order: Something went wrong"),
 *             @OA\Property(property="status", type="boolean", example=false)
 *         )
 *     )
 * )
 */

    public function paymentProcess(Request $request , Order $order)
    {
        //validate data before sending it

    $request->validate([
        'amount_cents' => 'required|numeric|min:1',
        'currency' => 'required|string|size:3',

        'delivery_needed' => 'required|in:true,false',

        'shipping_data.first_name' => 'required|string',
        'shipping_data.last_name' => 'required|string',
        'shipping_data.phone_number' => 'required|string|min:10',
        'shipping_data.email' => 'required|email',
    ]);
        if($order->user_id != $request->user()->id){
            return response()->json(['message' => 'Unauthorized To Pay This Product'], 401);
        }
        // if(!$order->canBePaid()){
        //     return response()->json(['message' => 'Order Can Not Be Paid'], 400);
        // }


        return $this->paymentGateway->sendPayment($request,$order);
    }

    public function callBack(Request $request)
    {
        $response = $this->paymentGateway->callBack($request);
        if ($response) {
            $order = Order::where('order_number', $response['order'])->get();
            Payment::create([
                'user_id' => $order[0]->user_id,
                'order_id' => $order[0]->id,
                'payment_method' => 'paymob',
                'payment_status' => PaymentStatus::COMPLETED,
            ]);
            Order::where('id', $order[0]->id)->update(['status' => OrderStatus::COMPLETED , 'payment_status' => PaymentStatus::COMPLETED]);
            return response()->json(['message' => 'Payment Success'], 200);
        }
        return response()->json(['message' => 'Payment Failed'], 400);
        }
    }


    // public function success()
    // {

    // }
    // public function failed()
    // {

    //     return view('payment-failed');
    // }


