<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $address = $user->delivery()->get()->toArray();
            return response()->json([
                'message' => 'user address',
                'is_success' => true,
                'status_code' => 200,
                'data' => $address
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'user address not found',
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            // 'user_id' => 'required|integer', // from request
            'postal_code' => 'nullable|integer|digits_between:0,32',
            'name' => 'required|string|max:64',
            'mobile_number' => 'required',
            'address' => 'required|string|max:255',
            'province_id' => 'required|integer',
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
            $res = Address::create([
                'user_id' => $request->user()->id,
                'postal_code' => $data['postal_code'],
                'name' => $data['name'],
                'mobile_number' => $data['mobile_number'],
                'address' => $data['address'],
                'province_id' => $data['province_id'],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => __('not_success'),
                    'is_success' => false,
                    'status_code' => 400,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json(
            [
                'message' => __('success'),
                'is_success' => true,
                'status_code' => 200,
            ]
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Address $address)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        //
    }
}
