<?php

namespace App\Http\Controllers\Api\v2\User;

use App\Http\Controllers\PurchaseRequestsController as PurchaseRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\purchase_requests;
use App\Models\Carts as cart;
use App\Models\product;

class PurchaseRequestsController extends PurchaseRequests
{
    /**
     * Create the purchase request, from user
     */

    public function create(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:128|min:5',
            'details' => 'required|string',
            'amount' => 'required_array_keys:field,value|array',
            'amount.field' => 'required|string|in:number,weight,volume',
            'amount.value' => 'required|integer',
            # (changed to nullable), We have decided not to make the quality option available, but we have set a value for it by default
            'quality' => 'nullable|string|in:acceptable,average,good,excellent,luxury',
            'delivery_date' => 'nullable|date|date_format:Y-m-d', // TODO: it's not be in past date
            'proposed_price' => 'nullable|decimal:0|between:0,9999999999',
            'image' => 'nullable|array',
            'image.*.photoUrl' => 'required|string', // TODO: check regex
            'category_id' => 'required|integer',
            'province_id' => 'required|integer',
            'address_id' => 'nullable|integer' // new address id column
        ];

        // 'active_time', // filled by the Admin when the request is approved
        // 'status', // filled by controller, By default is pending
        // 'features', // Not activated yet
        $dataOnly = $request->only(
            [
                'title',
                'details',
                'amount.field',
                'amount.value',
                'quality',
                'delivery_date',
                'proposed_price',
                'image',
                'category_id',
                'province_id',
                'address_id'
            ]
        );

        # We have decided not to make the quality option available, but we have set a value for it by default
        $dataOnly['quality'] = 'good';

        $validator = Validator::make($dataOnly, $rules);
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors(),
                    'is_success' => false,
                    'status_code' => 422,
                ],
            );
        }
        // normalize image fields
        $dataOnly['image'] = array_map(function ($item) {
            return ['photoUrl' => $item['photoUrl']];
        }, $dataOnly['image']);

        $dataOnly['user_id'] = $request->user()->id;
        $dataOnly['status'] = 'pending';
        // Create a new request
        $res = parent::mainCreate($dataOnly);

        return response()->json($res['res']);
    }

    /**
     * Show list of all purchase requests for the user.
     */
    public function userList(Request $request)
    {
        // It's string must be to convert to array
        $status = $request->route('status') ? [$request->route('status')] : ['pending', 'active', 'supplierpending', 'chosen'];
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

        // use user model to get self-signed information
        // $results =  $request->user()->purchase_requests($status)->orderBy('created_at', 'desc')->get();
        $userId = $request->user()->id;

        $results = parent::getPurchaseRequests($status)
        ->where('user_id', $userId)
        ->withCategory()
        ->withProvince()
        ->SelectSafePrev()
        ->get();
        // return $request->user()->purchase_requests()->paginate(10);

        return response()->json(
            [
                'message' => __('message.get_something', ['attribute' => __('message.attributes.products')]),
                'is_success' => true,
                'status_code' => 200,
                'data' => $results,
            ]
        );
    }

    /**
     * Show list of purchases for supplier,
     * this purchase is active and available
     * to set the offer by supplier
     *
     * @param Request $request
     */
    public function getSupplierList(Request $request)
    {
        $excludedUserId = $request->user()->id;

        /**
         * Tinker code
         * $user = 2;
         * purchase_requests::whereDoesntHave('purchase_offer', function ($query) use ($user) {
         *   $query->where('user_id', $user);
         *   })->where('user_id' ,'!=' ,3 )->where('status' , 'pending')->get();
         */
        // $interested = $request->user()->supplierProductsList->pluck('id')->all();
        $list = $request->user()->load(['supplierProductsCategoryIdOnly', 'purchase_offers_list_request_ids']);
        $interested = $list->supplierProductsCategoryIdOnly->pluck('category_id')->toArray();
        $offeredRequestIds = $list->purchase_offers->pluck('purchase_request_id')->toArray();

        $results = parent::getPurchaseRequests(['active'])
            ->where(function ($query) use ($excludedUserId) {
                // Condition 1: supplier_id = null => come from panel
                $query->whereNull('supplier_id');
                // Condition 2: supplier_id = UserId => come from shop
                $query->orWhere(function ($subQuery) use ($excludedUserId) {
                    $subQuery->where('supplier_id', $excludedUserId);
                });
            })
            ->whereIn('category_id', $interested)
            ->where('active_time', '>=', Carbon::now()->toDateString())
            // ->whereDoesntHave('purchase_offer', function ($query) use ($excludedUserId) { # Don't worked, and we not need this so ignore this
            //     $query->where('user_id', $excludedUserId);
            // })
            ->where('user_id', '!=', $excludedUserId)
            ->withCategory()
            ->withProvince()
            ->SelectSafePrev()
            ->get();

        $results->each(function ($offer) use ($offeredRequestIds) {
            $offer->have_offer = in_array($offer->id, $offeredRequestIds);
        });

        return response()->json([
            'message' => __('message.active_purchase_request_list'),
            'is_success' => true,
            'status_code' => 200,
            'data' => $results,
            'owened' => $offeredRequestIds
        ]);
    }

    /**
     * Show purchase request details for supplier
     */
    public function getActivePurchaseDetails(Request $request, int $id)
    {
        $excludedUserId = $request->user()->id;
        $rules = [
            'id' => 'required|integer|min:1' #|exists:purchase_requests,id'
        ];
        $data = [
            'id' => $id
        ];
        $validator = Validator::make($data, $rules);
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
        $interested = $request->user()->supplierProductsList->pluck('id')->all();
        $res = parent::getPurchaseRequest(['active'])
            ->where(function ($query) use ($excludedUserId) {
                // Condition 1: supplier_id = null => come from panel
                $query->whereNull('supplier_id');
                // Condition 2: supplier_id = UserId => come from shop
                $query->orWhere(function ($subQuery) use ($excludedUserId) {
                    $subQuery->where('supplier_id', $excludedUserId);
                });
            })
            ->whereIn('category_id', $interested)
            ->where('id', $id)
            ->where('active_time', '>=', Carbon::now()->toDateString())
            ->where('user_id', '!=', $excludedUserId)
            ->selectSafeDetails()
            ->withCategory()
            ->withProvince()
            ->with('product:id,price,media');
            // ->with('purchase_offer');

        $res = $res->first();
        $user_offer = $res->purchase_offer_user($excludedUserId);
        if ($res instanceof purchase_requests) {

            return response()->json([
                'message' => __('message.success'),
                'is_success' => true,
                'status_code' => 200,
                'data' => $res,
                'user_offer' => $user_offer,
                'have_user_offer' => ($user_offer === null ? false : true)
            ]);
        }
        return response()->json([
            'message' => __('message.not_success'),
            'is_success' => false,
            'status_code' => 400,
            'data' => null
        ]);
    }

    /**
     * Purchase request method from the shopping with direct supplier information
     */
    public function directPurchaseRequest(Request $request, cart $cart)
    {
        # input contains: amount, product_id, id (cart id not need), details, address_id
        $input = $request->all();
        /**
         * 'quality', not need set
         * 'delivery_date', not need set
         * 'proposed_price', not need set
         * 'image', not need set
         */
        $validator = Validator::make(
            [
                'details' => $input['details'],
                'amount' => [
                    'value' => $input['amount'],
                    'field' => 'number'
                ],
                'address_id' => $input['address_id'],
            ],
            [
                'details' => 'required|string',
                'amount' => 'required_array_keys:field,value|array',
                'amount.value' => 'required|integer',
                'amount.field' => 'required|string|in:number,weight,volume',
                'address_id' => 'required|integer',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors(),
                    'is_success' => false,
                    'status_code' => 422,
                ],
            );
        }

        try {
            $address = $request->user()->delivery()->where('id', $input['address_id'])->first()->toArray();
            # TODO: send sms to supplier and admin, DONE
            # TODO: check is product already in cart is active or not
            $product = product::select('id', 'user_id', 'category_id', 'is_actived', 'is_approved', 'title')->where('id', $cart->product_id)->first()->toArray();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'خطایی در پردازش رخ داده',
                    'is_success' => false,
                    'status_code' => 422,
                ],
            );
        }

        if (count($address) == 0) {
            return response()->json(
                [
                    'message' => 'مشکلی در آدرس دریافت پیش آمده',
                    'is_success' => false,
                    'status_code' => 422,
                ],
            );
        }

        $data = [
            'user_id' => $request->user()->id,
            'supplier_id' => $product['user_id'], // TODO:  get from product
            'details' => $input['details'],
            'amount' => [
                'value' => $input['amount'],
                'field' => 'number'
            ],
            'address_id' => $address['id'],
            'province_id' => $address['province']['id'],
            'category_id' => $product['category_id'],
            'title' => $product['title'],
            'product_id' => $cart->product_id,
            'status' => 'supplierpending',
            'active_time' => Carbon::now()->addDays(3)
        ];

        $res = parent::mainCreate($data);

        $result = response()->json($res['res']);
        if ($result) {
            $cart->delete();
        }
        return $result;
    }
}
