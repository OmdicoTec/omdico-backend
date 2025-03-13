<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Productmetaseo;
use Illuminate\Support\Facades\Validator;


class ProductmetaseoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(int $product_id)
    {
        $metaSeo = Productmetaseo::where('product_id', $product_id)->first();
        if ($metaSeo instanceof Productmetaseo) {
            return response()->json(
                [
                    'message' => __('success'),
                    'is_success' => true,
                    'init' => true,
                    'status_code' => 200,
                    'data' => $metaSeo->toArray(),
                ],
            );
        } else {
            return response()->json(
                [
                    'message' => __('not_found'),
                    'is_success' => true,
                    'init' => false,
                    'status_code' => 200,
                    'data' => [
                        'title' => '',
                        'description' => '',
                    ],
                ],
            );
        }
    }

    /**
     * create or update product metaseo
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Productmetaseo  $id
     * @return \Illuminate\Http\Response
     **/
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'title' => 'nullable|string|max:126',
            'description' => 'nullable|string|max:254',
            'product_id' => 'required|integer', # TODO: check is correct product_id
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

        try {
            $metaseo = Productmetaseo::updateorcreate(
                [
                    'product_id' => $data['product_id'],
                    'title' => $data['title'],
                    'description' => $data['description']
                ]
            );

            if ($metaseo instanceof Productmetaseo) {
                return response()->json(
                    [
                        'message' => __('message.success'),
                        'is_success' => true,
                        'status_code' => 200,
                        'data' => $metaseo->toArray(),
                    ],
                );
            } else {
                return response()->json(
                    [
                        'message' => __('message.not_success'),
                        'is_success' => true,
                        'status_code' => 200,
                        'data' => null,
                    ],
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'مشکلی در دیتا',
                    'is_success' => false,
                    'status_code' => 400,
                    'data' => null,
                ],
            );
        }
    }
}
