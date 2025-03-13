<?php

namespace App\Http\Controllers\Users\Supplier;

use App\Http\Controllers\ProductController as ProviderProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\product;

class ProductController extends ProviderProductController
{
    /**
     * Create new product with user_id, is_actived, is_approved, is_rejected
     *
     * @param Request $request
     *
     */
    public function storeBySupplier(Request $request)
    {
        $user_id = $request->user()->id;
        $is_actived = false;
        $is_approved = false;
        $is_rejected = false;

        // Validate request
        // title
        // category_id
        // category
        // price
        // time
        // features [{"title":"", value:""},{"title":"", value:""}]
        // limited
        // quantity
        // media{"image":[{photoUrl:""},{photoUrl:""}],"video":[]}
        // details
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:128',
            'category_id' => 'nullable|integer',
            'category' => 'nullable|string',
            'price' => 'nullable|integer',
            'time' => 'required|integer',
            'features' => 'nullable|array',
            'features.*.title' => 'required|string',
            'features.*.value' => 'required|string',
            'limited' => 'required|boolean',
            'quantity' => 'nullable|integer',
            'media' => 'nullable|array',
            'media.image' => 'nullable|array',
            'media.image.*.photoUrl' => 'required|string',
            'media.video' => 'nullable|array',
            'details' => 'required|string',
            'filters' => 'nullable|array',
            'filters.*.category_id' => 'required',
            'filters.*.characteristic_id' => 'required',
            'filters.*.characteristic_id.characteristics' => 'nullable|array',
            'filters.*.characteristic_id.characteristics.*.key' => 'required',
            'province_id'=> 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده صحیح نمی باشد', // TODO: change this message $validator->errors()
                'is_success' => false,
                'status_code' => 400,
                'data' => $validator->errors(),
            ]);
        }

        return parent::store($request, $user_id, $is_actived, $is_approved, $is_rejected);
    }

    public function searchProductsForInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:128|min:3', # title in DB
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده صحیح نمی باشد',
                'is_success' => false,
                'status_code' => 400,
                'data' => $validator->errors(),
            ]);
        }

        $products = product::where('title', 'LIKE', '%' . $request->search . '%')
        ->where('is_actived', true)
        ->where('is_approved', true)
        ->where('user_id', $request->user()->id)
        ->select('id','title','price', 'limited')
        ->take(4)->get();

        return response()->json([
            'message' => __('message.found'),
            'is_success' => true,
            'status_code' => 200,
            'data' => $products->all(),
        ]);
    }
}
