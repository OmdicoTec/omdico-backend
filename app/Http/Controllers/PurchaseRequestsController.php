<?php

namespace App\Http\Controllers;

use App\Models\purchase_requests;
// use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * This is the main controller for purchase requests
 * in this part haven't validation rules
 *
 */
class PurchaseRequestsController extends Controller
{
    /**
     * Define a rull for status variables
     */
    protected $status = 'pending,active,choice,prepayment,precheckout,successful,returned,unsuccessful,supplierpending';

    public static $publikStatus = 'pending,active,choice,prepayment,precheckout,successful,returned,unsuccessful,supplierpending';
    public function mainCreate($data)
    {
        try {
            $count = purchase_requests::where('user_id', $data['user_id'])->whereIn('status', ['pending','active'])->count();
            if ($count >= 5) {
                return [
                    'data' => '',
                    'res' => [
                        'message' => __('message.create_something_limited', ['attribute' => __('message.attributes.purchase_request')]),
                        'is_success' => false,
                        'status_code' => 400,
                        'data' => null, // seperated for security reasons
                    ]
                ];
            }
            // Create a new purchase_request and fill it with the request data
            $purchaseRequest = purchase_requests::create($data);

            // Return the newly created purchase_request
            // return $purchaseRequest;
            return [
                'data' => $purchaseRequest,
                'res' => [
                    'message' => __('message.create_something', ['attribute' => __('message.attributes.purchase_request')]),
                    'is_success' => true,
                    'status_code' => 200,
                    'data' => null, // seperated for security reasons
                ]
            ];

        } catch (\Throwable $th) {
            // return response()->json([
            return [
                'res' =>[
                    'message' => __('message.create_something_error', ['attribute' => __('message.attributes.purchase_request')]),
                    'is_success' => false,
                    'status_code' => 500,
                    'data' => null, // seperated for security reasons
                ],
                'data' => $th->getMessage(),
            ];
        }

    }

    /**
     * Get a list of Purchases with special status codes.
     *
     * @param array $status
     */
    public function getPurchaseRequests(array $staus = [])
    {
        if (empty($staus)) {
            return purchase_requests::orderBy('created_at', 'desc');
        }
        return purchase_requests::whereIn('status', $staus)->orderBy('created_at', 'desc');
    }

    /**
     * Get a purchase.
     *
     * @param array $status
     */
    public function getPurchaseRequest(array $staus = [])
    {
        if (empty($staus)) {
            return purchase_requests::class;
        }
        return purchase_requests::whereIn('status', $staus);
    }

    public static function getStatus()
    {
        return self::$publikStatus;
    }
}
