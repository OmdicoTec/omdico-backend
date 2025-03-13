<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\purchase_offers;
use App\Models\purchase_requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\product;
use Carbon\Carbon;

class OffersController extends Controller
{

    public function customerOffers(purchase_requests $id)
    {
        // TODO: maybe need have rule for purchase_requests and purchase_offers
        $offers = $id->purchase_offer()->get()->makeHidden('user_id')->all();
        return response()->json([
            'message' => __('message.found'),
            'is_success' => true,
            'status_code' => 200,
            'data' => $offers,
            'purchase_request' => $id
        ]);
    }

    public function customerOfferConfirmInvoice(purchase_requests $id, Request $request)
    {
        $purchase_request = $id;
        $validator = Validator::make($request->all(), [
            'purchase_offer_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors(),
                    'is_success' => false,
                    'status_code' => 422,
                ],
            );
        }
        # check valid time, check active, check have not active invoice
        if ($purchase_request->status === 'active') {
            $offer = $purchase_request->purchase_offer()->where('id', $request->purchase_offer_id)->first();
            // ->exists();
            if ($offer) {
                DB::transaction(function () use ($purchase_request, $offer) {
                    $purchase_request->update([
                        'status' => 'chosen',
                        'purchase_offer_id' => $offer->id
                    ]);
                    $offer->update([
                        'is_winner' => true,
                        'status' => 'active'
                    ]);
                });

                return response()->json([
                    'message' => __('message.confirm_this_invoice'),
                    'is_success' => true,
                    'status_code' => 201,
                    'data' => $validator->validated()
                ]);
            }
        }elseif($purchase_request->status === 'chosen'){
            return response()->json([
                'message' => __('message.confirm_this_invoice'),
                'is_success' => false,
                'status_code' => 201,
                'data' => $validator->validated()
            ]);
        }
        return response()->json([
            'message' => __('message.cant_confirm_this_invoice_for_this_time'),
            'is_success' => false,
            'status_code' => 409,
            'data' => null
        ]);
    }
}
