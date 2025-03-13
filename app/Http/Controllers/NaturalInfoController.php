<?php

namespace App\Http\Controllers;

use App\Models\NaturalInfo;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StatusInfoController;


/**
 * TODO:
 * 1- add is_legal_person for user
 */
class NaturalInfoController extends Controller
{
    // table name is: natural_infos
    protected $table = 'nature_infos';

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
        $type = $user->type;
        // Before create new record, we must check if the user has already filled the form or not
        $exists = $user->natural()->exists();
        if ($exists) {
            return response()->json([
                'message' => 'شما قبلا اطلاعات مالک کسب و کار را ثبت کرده اید.',
                'is_success' => false,
                'is_already_filled' => true,
                'status_code' => 400,
            ]);
        } else {
            $data = $request->all();
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:55',
                'last_name' => 'required|max:55',
                'national_code' => 'required|numeric|digits:10|unique:natural_infos',
                'mobile_number' => 'nullable|numeric|digits:11',
                'other_mobile_number' => 'nullable|numeric|digits:11',
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
            $user->natural()->create(
                [
                    'user_id' => $user->id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'national_code' => $data['national_code'],
                    'mobile_number' => array_key_exists('mobile_number', $data) ? $data['mobile_number'] : $user->mobile_number,
                    'other_mobile_number' => array_key_exists('other_mobile_number', $data) ? $data['other_mobile_number'] : $user->mobile_number,
                    'is_legal_person' => $user->type == 'seller' ? true : false,
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
                'message' => 'اطلاعات مالک کسب و کار با موفقیت ثبت شد.',
                'is_success' => true,
                'status_code' => 200,
            ]);
        }
    }

    /**
     * Show the current user's natural info
     * Its for user seller/customer to show their info
     *
     */
    public function show(Request $request)
    {
        $user = $request->user();
        // check user status is there or not, auto check and create if not exists
        $this->checkUserStatus($request);
        $user_nature = $user->natural();
        if (!$user_nature->exists()) {
            return response()->json([
                'message' => 'اطلاعات رابط شرکت یافت نشد',
                'status' => [],
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات رابط شرکت',
            'status' => $user->status()->where('table_name', $this->table)->get()->first(),
            'is_success' => true,
            'status_code' => 200,
            'data' => $user_nature->get()->first(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|max:55|alpha',
            'last_name' => 'nullable|max:55|alpha',
            'national_code' => 'nullable|numeric|digits:10',
            'mobile_number' => 'nullable|numeric|digits:11',
            'other_mobile_number' => 'nullable|numeric|digits:11',
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
        // get user
        $user = $request->user();
        // check user status is there or not
        if ($user->natural()->exists() && count($data) > 0) {
            $natural_status = $user->status()->where('table_name', $this->table)->get()->first();
            if ($natural_status->is_approved && $natural_status->is_editable) {
                $natural = $user->natural()->get()->first();
                // update status table
                $natural_status->update(
                    [
                        "is_editable" => false,
                        "is_failed" => false,
                        "data" => json_encode([
                            'first_name' => array_key_exists('first_name', $data) ? $data['first_name'] : $natural->first_name,
                            'last_name' => array_key_exists('last_name', $data) ? $data['last_name'] : $natural->last_name,
                            'national_code' => array_key_exists('national_code', $data) ? $data['national_code'] : $natural->national_code,
                            'mobile_number' => array_key_exists('mobile_number', $data) ? $data['mobile_number'] : $natural->mobile_number,
                            'other_mobile_number' => array_key_exists('other_mobile_number', $data) ? $data['other_mobile_number'] : $natural->other_mobile_number,
                        ]),
                    ]
                );

                return response()->json([
                    'message' => 'اطلاعات مالک کسب و کار با موفقیت ویرایش شد. اطلاعات شما در حال بررسی می باشد.',
                    'is_success' => true,
                    'status_code' => 200,
                ]);
            }
            return response()->json([
                'message' => 'امکان ویرایش اطلاعات مالک کسب و کار وجود ندارد.',
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات مالک کسب و کار یافت نشد.',
            'is_success' => false,
            'status_code' => 400,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NaturalInfo $naturalInfo)
    {
        // Todo : use this for update
        // $natural->update(
        // [
        //     'first_name' => array_key_exists('first_name', $data) ? $data['first_name'] : $natural->first_name,
        //     'last_name' => array_key_exists('last_name', $data) ? $data['last_name'] : $natural->last_name,
        //     'national_code' => array_key_exists('national_code', $data) ? $data['national_code'] : $natural->national_code,
        //     'mobile_number' => array_key_exists('mobile_number', $data) ? $data['mobile_number'] : $natural->mobile_number,
        //     'other_mobile_number' => array_key_exists('other_mobile_number', $data) ? $data['other_mobile_number'] : $natural->other_mobile_number,
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
            'first_name' => 'required|max:55',
            'last_name' => 'required|max:55',
            'national_code' => 'required|numeric|digits:10',
            'mobile_number' => 'nullable|numeric|digits:11',
            'other_mobile_number' => 'nullable|numeric|digits:11',
            'is_legal_person' => 'required|in:true,false',
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

        $natural = $user->natural();
        if ($natural->exists()){
            $natural->get()->first()->update(
                [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'national_code' => $request->national_code,
                    'mobile_number' => $request->mobile_number,
                    'other_mobile_number' => $request->other_mobile_number,
                    'is_legal_person' => $request->is_legal_person == 'true' ? true : false,
                ]
            );
        }else{
            $natural->create(
                [
                    'user_id' => $user->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'national_code' => $request->national_code,
                    'mobile_number' => $request->mobile_number,
                    'other_mobile_number' => $request->other_mobile_number,
                    'is_legal_person' => $request->is_legal_person == 'true' ? true : false,
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
            'message' => 'اطلاعات مالک کسب و کار با موفقیت ویرایش شد.',
            'is_success' => true,
            'status_code' => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NaturalInfo $naturalInfo)
    {
        //
    }

    private function checkUserStatus(Request $request): void
    {
        $status_controller = new StatusInfoController();
        $res = $status_controller->checkUserStatus($request);
    }
}
