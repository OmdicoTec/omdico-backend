<?php

namespace App\Http\Controllers\Api\v2\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\SupplierProduct;
use App\Models\category;

class SupplierProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $list = $request->user()->supplierProductsList->makeHidden('pivot');
        $list = $request->user()->supplierProducts->makeHidden(['category_id', 'user_id']);

        return response()->json([
            'message' => 'Supplier products interested list.',
            'is_success' => true,
            'status_code' => 200,
            'data' => $list
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, category $id)
    {
        // TODO: check category is active
        $res = SupplierProduct::updateOrCreate([
            'user_id' => $request->user()->id,
            'category_id' => $id->id
        ]);

        return response()->json([
            'message' => __('message.create_something', ['attribute' => '']),
            'is_success' => true,
            'status_code' => 200,
            'data' => $res
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, SupplierProduct $id)
    {
        $user_id = $request->user()->id;
        if ($user_id === $id->user_id) {
            $delete = $id->delete();
            return response()->json([
                'message' => __('message.deleted'),
                'is_success' => true,
                'status_code' => 200,
                'data' => $delete
            ], 200);
        } else {
            return response()->json([
                'message' => __('message.not_success'),
                'is_success' => true,
                'status_code' => 400,
                'data' => null
            ]);
        }
    }
}
