<?php

namespace App\Interfaces;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function sendPayment(Request $request,Order $order);

    public function callBack(Request $request);
}
