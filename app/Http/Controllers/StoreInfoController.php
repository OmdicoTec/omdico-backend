<?php

namespace App\Http\Controllers;

use App\Models\StoreInfo;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StatusInfoController;
class StoreInfoController extends Controller
{
    // table name is: store_infos
    protected $table = 'store_infos';
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
     * Table name: store_infos
     *
     */
    public function store(Request $request)
    {

        $user = $request->user();
        $type = $user->type;
        // Before create new record, we must check if the user has already filled the form or not
        $exists = $user->store()->exists();
        if ($exists) {
            return response()->json([
                'message' => 'شما قبلا اطلاعات فروشگاه را ثبت کرده اید.',
                'is_success' => false,
                'is_already_filled' => true,
                'status_code' => 400,
            ]);
        } else {
            $data = $request->all();
            // seller code, created by system randomly and unique
            $validator = Validator::make($request->all(), [
                'store_name' => 'required|max:55|string', // TODO: unique store name
                'phone_number' => 'nullable|string|max:55',
                'working_days' => 'nullable|max:255',
                'website' => 'nullable|max:255',
                'about_store' => 'nullable|max:500',
                'activity_area' => 'required|max:255',
                'category' => 'required|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'message' => $validator->errors(),
                        'is_success' => false,
                        'status_code' => 422,
                    ]
                );
            }
            // array_key_exists
            // TODO: createOrUpdate method, berfore create new record, check if exists...
            // relation must be hasOne
            $user->store()->create(
                [
                    // 'mobile_number' => array_key_exists('mobile_number', $data) ? $data['mobile_number'] : $user->mobile_number,
                    'user_id' => $user->id,

                    //
                    'store_name' => $data['store_name'], // req
                    'phone_number' => array_key_exists('phone_number', $data) ? $data['phone_number'] : $user->phone_number,
                    'working_days' => array_key_exists('working_days', $data) ? $data['working_days'] : null,
                    'website' => array_key_exists('website', $data) ? $data['website'] : null,
                    'about_store' => array_key_exists('about_store', $data) ? $data['about_store'] : null,
                    'activity_area' => $data['activity_area'], // req
                    'category' => $data['category'], // req
                    // random seller code unique search
                    // Todo: add unique to seller_code
                    'seller_code' => rand(1111111, 9999999),
                ]
            );

            // if create new record is success, we must update status table fo lock this step
            // Because Admin must check the user information and approve it
            $user->status()->where('table_name', $this->table)->get()->first()->update(
                [
                    "is_approved" => false,
                    "is_editable" => false,
                    "is_failed" => false,
                ]
            );

            return response()->json([
                'message' => 'اطلاعات فروشگاه با موفقیت ثبت شد.',
                'is_success' => true,
                'status_code' => 200,
            ]);
        }
    }

    /**
     * Show the current user's store info
     * Its for user seller/customer to show their info
     *
     */
    public function show(Request $request)
    {
        $user = $request->user();
        // check user status is there or not, auto check and create if not exists
        $this->checkUserStatus($request);
        $user_store = $user->store();
        if (!$user_store->exists()) {
            return response()->json([
                'message' => 'اطلاعات فروشگاه شما ثبت نشده است.',
                'status' => [],
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات مرتبط فروشگاه',
            'status' => $user->status()->where('table_name', $this->table)->get()->first(),
            'is_success' => true,
            'status_code' => 200,
            'data' => $user_store->get()->first(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'store_name' => 'nullable|max:55|alpha',
            'phone_number' => 'nullable|numeric|max:55',
            'working_days' => 'nullable|max:255',
            'website' => 'nullable|max:255',
            'about_store' => 'nullable|max:500',
            'activity_area' => 'nullable|max:255',
            'category' => 'nullable|max:255',
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
        if ($user->store()->exists() && count($data) > 0) {
            $store_status = $user->status()->where('table_name', $this->table)->get()->first();
            if ($store_status->is_approved && $store_status->is_editable) {
                $store = $user->store()->get()->first();
                // update status table
                $store_status->update(
                    [
                        "is_editable" => false,
                        "is_failed" => false,
                        "data" => json_encode([
                            'store_name' => array_key_exists('store_name', $data) ? $data['store_name'] : $store->store_name,
                            'phone_number' => array_key_exists('phone_number', $data) ? $data['phone_number'] : $store->phone_number,
                            'working_days' => array_key_exists('working_days', $data) ? $data['working_days'] : $store->working_days,
                            'website' => array_key_exists('website', $data) ? $data['website'] : $store->website,
                            'about_store' => array_key_exists('about_store', $data) ? $data['about_store'] : $store->about_store,
                            'activity_area' => array_key_exists('activity_area', $data) ? $data['activity_area'] : $store->activity_area,
                            'category' => array_key_exists('category', $data) ? $data['category'] : $store->category,
                        ]),
                    ]
                );

                return response()->json([
                    'message' => 'اطلاعات فروشگاه با موفقیت ویرایش شد. اطلاعات شما در حال بررسی می باشد.',
                    'is_success' => true,
                    'status_code' => 200,
                ]);
            }
            return response()->json([
                'message' => 'امکان ویرایش اطلاعات فروشگاه وجود ندارد.',
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات فروشگاه یافت نشد.',
            'is_success' => false,
            'status_code' => 400,
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreInfo $storeInfo)
    {
        // $store->update(
        //     [
        //         'store_name' => array_key_exists('store_name', $data) ? $data['store_name'] : $store->store_name,
        //         'phone_number' => array_key_exists('phone_number', $data) ? $data['phone_number'] : $store->phone_number,
        //         'working_days' => array_key_exists('working_days', $data) ? $data['working_days'] : $store->working_days,
        //         'website' => array_key_exists('website', $data) ? $data['website'] : $store->website,
        //         'about_store' => array_key_exists('about_store', $data) ? $data['about_store'] : $store->about_store,
        //         'activity_area' => array_key_exists('activity_area', $data) ? $data['activity_area'] : $store->activity_area,
        //         'category' => array_key_exists('category', $data) ? $data['category'] : $store->category,
        //     ]
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
            'store_name' => 'required|max:55|string',
            'phone_number' => 'nullable|string|max:55',
            'working_days' => 'nullable|max:255',
            'website' => 'nullable|max:255',
            'about_store' => 'nullable|max:500',
            'activity_area' => 'required|max:255',
            'category' => 'required|max:255',
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

        $store = $user->store();
        if ($store->exists()){
            $store->update(
                [
                    'store_name' => $request->store_name,
                    'phone_number' => $request->phone_number,
                    'working_days' => $request->working_days,
                    'website' => $request->website,
                    'about_store' => $request->about_store,
                    'activity_area' => $request->activity_area,
                    'category' => $request->category,
                ]
            );
        }else{
            $store->create(
                [
                    'user_id' => $user->id,
                    'store_name' => $request->store_name,
                    'phone_number' => $request->phone_number,
                    'working_days' => $request->working_days,
                    'website' => $request->website,
                    'about_store' => $request->about_store,
                    'activity_area' => $request->activity_area,
                    'category' => $request->category,
                    'seller_code' => rand(1111111, 9999999),
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
            'message' => 'اطلاعات فروشگاه با موفقیت ویرایش شد.',
            'is_success' => true,
            'status_code' => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoreInfo $storeInfo)
    {
        //
    }
    private function checkUserStatus(Request $request): void
    {
        $status_controller = new StatusInfoController();
        $res = $status_controller->checkUserStatus($request);
    }
}
