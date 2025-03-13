<?php

namespace App\Http\Controllers\Api\v2\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Carts;
use App\Models\product;
class CartsController extends Controller
{

    /**
     * Carts maker
     */

    public function basket(Request $request)
    {
        $user = $request->user();
        $dataOnly = $request->only(
            [
                'basket',
            ]
        );
        $validator = Validator::make($dataOnly, [
            'basket' => 'nullable|array',
            'basket.*.product_id' => 'required|integer|min:1',
            'basket.*.quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده صحیح نیست.',
                'is_success' => false,
                'status' => 422,
                'errors' => $validator->errors(),
                // 'data' => $this->reshape($dataOnly['basket'])
            ]);
        }

        $dataOnly = $this->reshape($dataOnly['basket'], $user->id);
        $carts = $user->Carts()->get();
        // it's contained list of products to show with detailed information
        $productsIdList = [];
        foreach ($carts as $item) {
            if (array_key_exists($item->product_id, $dataOnly)) {
                // if quantity is lower than 1 then remove the item from the basket, else
                // update the basket
                if ($dataOnly[$item->product_id]['quantity'] > 0) {
                    $item->update([
                        'quantity' => $dataOnly[$item->product_id]['quantity']
                    ]);
                    $productsIdList[] = $item->product_id;
                } else {
                    $item->delete();
                }
                // pop the item from the basket $dataOnly, Because
                // maybe have new basket to add with method firstOrCreate
                unset($dataOnly[$item->product_id]);
            } else {
                // this product is already in the basket and we don't want to remove or update it again
                $productsIdList[] = $item->product_id;
            }
        }

        $basket = new Carts();
        foreach ($dataOnly as $item) {
            if ($item['quantity'] > 0){
                try {
                    $res = $basket->firstOrCreate([
                        'product_id' => $item['product_id'],
                        'user_id' => $item['user_id'],
                        'quantity' => $item['quantity']
                    ]);

                    if ($res) {
                        $productsIdList[] = $item['product_id'];
                        unset($dataOnly[$item['product_id']]);
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    unset($dataOnly[$item['product_id']]);
                }
            }
        }

        return response()->json([
            'message' => 'myBasket',
            'is_success' => true,
            'status' => 200,
            'data' => $basket->where('user_id', $user->id)->withProduct()->get()->toArray(),
        ]);
        /**
         * max_valid_count
         * insurance_count
         * insurance_price
         * code
         * discount
         * marketing_group
         */
    }

    public function basketGhost(Request $request)
    {
        $dataOnly = $request->only(
            [
                'basket',
            ]
        );
        $productsId = $request->only(
            [
                'basket.*.product_id',
            ]
        )['basket']['*']['product_id'];

        $validator = Validator::make($dataOnly, [
            'basket' => 'nullable|array',
            'basket.*.product_id' => 'required|integer|min:1',
            'basket.*.quantity' => 'required|integer|min:0',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده صحیح نیست.',
                'is_success' => false,
                'status' => 422,
                'errors' => $validator->errors(),
            ]);
        }

        $products = product::selectSafePrev()->whereIn('id',$productsId)->get()->toArray();
        $dataOnly = $this->reshape($dataOnly['basket']);
        $res = [];
        foreach ($products as $item) {
            // dd($item);
            $res[] = [
                'id' => $item['id'],
                'product_id' => $item['id'],
                'quantity' => $dataOnly[$item['id']]['quantity'],
                'products' => $item
            ];
        }

        return response()->json([
            'message' => 'ghostBasket',
            'is_success' => true,
            'status' => 200,
            'data' => $res,
        ]);

    }
    /**
     * Reshape input data
     */
    private function reshape($data, $user_id = null)
    {
        $reshapedData = [];

        foreach ($data as $item) {
            $productId = $item['product_id'];
            $reshapedData[$productId] = $item;
            // add user_id to product
            $reshapedData[$productId]['user_id'] = $user_id;
        }
        return $reshapedData;
    }
}
