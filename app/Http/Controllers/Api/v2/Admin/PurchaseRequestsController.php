<?php

namespace App\Http\Controllers\Api\v2\Admin;

// use App\Http\Controllers\Controller;
use App\Http\Controllers\PurchaseRequestsController as PurchaseRequests;
use Illuminate\Http\Request;
use App\Models\purchase_requests;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PurchaseRequestsController extends PurchaseRequests
{

    /**
     * Get all purchase requests
     *
     * Suport pagination
     * inputs: pending,active,choice,prepayment,precheckout,successful,returned,unsuccessful ... in .$this->status
     *
     * @param Request $request
     */
    public function listOfPurchase(Request $request)
    {
        # TODO: Add support for pagination and username
        $status = $request->route('status') ? [$request->route('status')] : [];

        if ($status[0] === 'pending') {
            $status = ['pending', 'supplierpending'];
        }

        $rule = ['status' => 'nullable|in:' . $this->status];

        $validator = Validator::make($status, $rule);
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors()->first(),
                    'is_success' => false,
                    'status_code' => 422,
                ],
            );
        }

        $PurchaseRequests = parent::getPurchaseRequests($status)
            ->withCategory()
            ->withProvince()
            ->get();

        return response()->json(
            [
                'message' => __('message.get_something', ['attribute' => __('message.attributes.products')]),
                'is_success' => true,
                'status_code' => 200,
                'data' => $PurchaseRequests
            ]
        );
    }

    /**
     * Get purchase request all information
     *
     */
    public function getPurchaseRequestInformation(Request $request)
    {
        // dd($request->route('status'),  $request->route('purchase_id'));
        $status = $request->route('status') ? $request->route('status') : 'pending';
        $data = [
            'status' => $status,
            'purchase_id' => $request->route('purchase_id')
        ];
        // Now you can use $status and $purchaseId in your logic
        $rules = [
            'status' => 'nullable|in:' . $this->status,
            'purchase_id' => 'required|integer|min:1'
        ];

        $validator = Validator::make($data, $rules);
        // only can get array, beacuse use whereIn
        $data['status'] = [$data['status']];

        if ($validator->fails()) {
            // Handle validation errors here
            return response()->json(
                [
                    'message' => $validator->errors(),
                    'is_success' => false,
                    'status_code' => 400,
                    'data' => null
                ]
            );
        }
        if ($status === 'pending') {
            $data['status'] = ['pending', 'supplierpending'];
        }
        $PurchaseRequestsInfo = parent::getPurchaseRequests($data['status'])
            ->where('id', $data['purchase_id'])
            ->withCategory()
            ->withProvince()
            ->with('user')
            ->with('address')
            ->get()->first();
        if ($PurchaseRequestsInfo instanceof purchase_requests) {
            return response()->json(
                [
                    'message' => 'جزئیات درخواست خرید.',
                    'is_success' => true,
                    'status_code' => 200,
                    'data' => $PurchaseRequestsInfo
                ]
            );
        } else {
            return response()->json(
                [
                    'message' => 'درخواست خرید یافت نشد.',
                    'is_success' => false,
                    'status_code' => 400,
                    'data' => null
                ]
            );
        }
    }

    /**
     * Do active purchase request
     */
    public function doActiveStatus(Request $request)
    {
        $rules = [
            'id' => 'required|integer|min:1' #|exists:purchase_requests,id'
        ];
        $dataOnly = $request->only(array_keys($rules));
        $validator = Validator::make($dataOnly, $rules);
        if ($validator->fails()) {
            // Handle validation errors here
            return response()->json(
                [
                    'message' => $validator->errors(),
                    'is_success' => false,
                    'status_code' => 400,
                    'data' => null
                ]
            );
        }

        $purchase = purchase_requests::find($dataOnly['id']);

        if ($purchase instanceof purchase_requests) {
            if (empty($purchase->active_time) || ($purchase->status === 'supplierpending') || ($purchase->status === 'pending')) {
                try {
                    $res = $purchase->update([
                        'status' => 'active',
                        'active_time' => Carbon::tomorrow()->addDays(3)
                    ]);

                    if ($res) {
                        return response()->json(
                            [
                                'message' => __('message.updated'),
                                'is_success' => true,
                                'status_code' => 200,
                                'data' => null
                            ]
                        );
                    }
                } catch (\Exception $e) {
                    return response()->json(
                        [
                            'message' => $e,
                            'is_success' => false,
                            'status_code' => 400,
                            'data' => null
                        ]
                    );
                }
            }
        }
        return response()->json(
            [
                'message' => '*: مشکلی پیش آمد و یا درخواست یافت نشد',
                'is_success' => false,
                'status_code' => 400,
                'data' => null
            ]
        );
    }

    /**
     * Do delete pending purchase request
     */
    public function doDeleteOnlyPending(Request $request)
    {
        $rules = [
            'id' => 'required|integer|min:1' #|exists:purchase_requests,id'
        ];
        $dataOnly = $request->only(array_keys($rules));
        $validator = Validator::make($dataOnly, $rules);
        if ($validator->fails()) {
            // Handle validation errors here
            return response()->json(
                [
                    'message' => $validator->errors(),
                    'is_success' => false,
                    'status_code' => 400,
                    'data' => null
                ]
            );
        }

        $purchase = purchase_requests::whereIn('status', ['supplierpending', 'pending'])->where('id', $dataOnly['id']);
        $invoice = $purchase->first();
        if ($invoice instanceof purchase_requests) {
            $res = $invoice->update([
                'status' => 'unsuccessful'
            ]);
            if ($res) {
                return response()->json(
                    [
                        'message' => __('message.request_cancelled'),
                        'is_success' => true,
                        'status_code' => 200,
                        'data' => null
                    ]
                );
            }
        } else {
            return response()->json(
                [
                    'message' => __('message.not_found_item', ['attribute' => __('message.attributes.cancel')]),
                    'is_success' => false,
                    'status_code' => 400,
                    'data' => null
                ]
            );
        }
    }
}
