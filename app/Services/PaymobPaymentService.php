<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PaymobPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    /**
     * Create a new class instance.
     */
    protected $api_key;
    protected $integrations_id;

    public function __construct()
    {
        $this->base_url = config('app.paymob_base_url');
        $this->api_key = config('app.paymob_api_key');
        $this->header = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $this->integrations_id = [5201830, 5201827];
    }



    //build request
    protected function buildRequestForLogin(): \Illuminate\Http\JsonResponse
    {

        try {
            //type ? json || form_params
            $response = Http::withHeaders($this->header)->send('POST', $this->base_url . '/api/auth/tokens', [
                'json' => [
                    'api_key' => $this->api_key,
                ]
            ]);
            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
            ], $response->status());
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    //first generate token to access api
    protected function generateToken()
    {
        $response = $this->buildRequestForLogin();
        return $response->getData(true)['data']['token'];
    }

    //send payment
    public function sendPayment(Request $request, Order $order)
    {
        // return $this->generateToken();
        $this->header['Authorization'] = 'Bearer ' . $this->generateToken();
        //validate data before sending it
        $data = $request->all();
        $data['api_source'] = "INVOICE";
        $data['integrations'] = $this->integrations_id;

        $response = $this->buildRequest('POST', '/api/ecommerce/orders', $data);
        //handel payment response data and return it
        if ($response->getData(true)['success']) {


            $order->update(['order_number' => $response->getData(true)['data']['id']]);
            return ['success' => true, 'url' => $response->getData(true)['data']['url']];
        }

        return ['success' => false, 'url' => null];
    }


    //call back function
    public function callBack(Request $request)
    {
        $response = $request->all();
        Storage::put('paymob_response.json', json_encode($request->all()));

        if (isset($response['success']) && $response['success'] === 'true') {
            return $response;
        }
        return false;
    }
}
