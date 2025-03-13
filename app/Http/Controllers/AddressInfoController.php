<?php

namespace App\Http\Controllers;

use App\Models\AddressInfo;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StatusInfoController;

class AddressInfoController extends Controller
{

    // table name is: address_infos
    protected $table = 'address_infos';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * This function for user seller/customer to fill the form
     * Store a newly created resource
     * Table name: nature_infos
     *
     */
    public function store(Request $request)
    {

        $user = $request->user();
        // Before create new record, we must check if the user has already filled the form or not
        $exists = $user->address()->exists();
        if ($exists) {
            return response()->json([
                'message' => 'شما قبلا اطلاعات نشانی  را ثبت کرده اید.',
                'is_success' => false,
                'is_already_filled' => true,
                'status_code' => 400,
            ]);
        } else {
            $data = $request->all();
            $validator = Validator::make($request->all(), [
                'address' => 'required|max:512',
                'warehouse_address' => 'nullable|max:512',
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
            // array_key_exists
            $user->address()->create(
                [
                    'user_id' => $user->id,
                    'address' => $data['address'],
                    'warehouse_address' => $data['warehouse_address'],
                ]
            );

            // if create new record is success, we must update status table fo lock this step
            // Todo: createorupdate or check if exists or not
            // Because Admin must check the user information and approve it
            $user->status()->where('table_name', $this->table)->get()->first()->update(
                [
                    "is_approved" => false,
                    "is_editable" => false,
                    "is_failed" => false,
                ]
            );

            return response()->json([
                'message' => 'اطلاعات نشانی  با موفقیت ثبت شد.',
                'is_success' => true,
                'status_code' => 200,
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $user = $request->user();
            // check user status is there or not, auto check and create if not exists
            $this->checkUserStatus($request);
            $user_address = $user->address();
            if (!$user_address->exists()) {
                return response()->json([
                    'message' => 'اطلاعات نشانی شما ثبت نشده است.',
                    'status' => [],
                    'is_success' => false,
                    'status_code' => 400,
                ]);
            }
            return response()->json([
                'message' => 'اطلاعات مرتبط با نشانی',
                'status' => $user->status()->where('table_name', $this->table)->get()->first(),
                'is_success' => true,
                'status_code' => 200,
                'data' => $user_address->get()->first(),
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'address' => 'nullable|max:512',
            'warehouse_address' => 'nullable|max:512',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors(),
                    'is_success' => false,
                    'status_code' => 422,
                ],
                422
            );
        }
        // get user
        $user = $request->user();
        // check user status is there or not
        if ($user->address()->exists() && count($data) > 0) {
            $address_status = $user->status()->where('table_name', $this->table)->get()->first();
            if ($address_status->is_approved && $address_status->is_editable) {
                $address = $user->address()->get()->first();
                // update status table
                $address_status->update(
                    [
                        "is_editable" => false,
                        "is_failed" => false,
                        "data" => json_encode([
                            'address' => array_key_exists('address', $data) ? $data['address'] : $address->address,
                            'warehouse_address' => array_key_exists('warehouse_address', $data) ? $data['warehouse_address'] : $address->warehouse_address,
                        ]),
                    ]
                );

                return response()->json([
                    'message' => 'اطلاعات نشانی با موفقیت ویرایش شد. اطلاعات شما در حال بررسی می باشد.',
                    'is_success' => true,
                    'status_code' => 200,
                ]);
            }
            return response()->json([
                'message' => 'امکان ویرایش اطلاعات نشانی وجود ندارد.',
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات نشانی یافت نشد.',
            'is_success' => false,
            'status_code' => 400,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AddressInfo $addressInfo)
    {
        // Todo : use this for update
        // $address->update(
        // [
        //     'address' => array_key_exists('address', $data) ? $data['address'] : $address->address,
        //     'warehouse_address' => array_key_exists('warehouse_address', $data) ? $data['warehouse_address'] : $address->warehouse_address,
        // ]
        // );
    }

    /**
     * Update info by admin
     * Notice:
     * this function is't call directly from route!
     *
     * @param $request
     * @param $user
     */
    public function updateByAdmin($request, $user)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|max:512',
            'warehouse_address' => 'nullable|max:512',
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

        $address = $user->address();
        if ($address->exists()) {
            $address->get()->first()->update(
                [
                    'address' => $request->address,
                    'warehouse_address' => $request->warehouse_address,
                ]
            );

        }else{
            $address->create(
                [
                    'user_id' => $user->id,
                    'address' => $request->address,
                    'warehouse_address' => $request->warehouse_address,
                ]
            );
        }
        // update status table
        $natural_status = $user->status()->where('table_name', $this->table)->get()->first();
        $natural_status->update(
            [
                "is_approved" => true,
                "is_editable" => true,
                "is_failed" => false,
                "data" => null,
                "note" => ""
            ]
        );
        return response()->json([
            'message' => 'اطلاعات نشانی با موفقیت ویرایش شد.',
            'is_success' => true,
            'status_code' => 200,
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AddressInfo $addressInfo)
    {
        //
    }

    private function checkUserStatus(Request $request): void
    {
        $status_controller = new StatusInfoController();
        $res = $status_controller->checkUserStatus($request);
    }
}
