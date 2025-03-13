<?php

namespace App\Http\Controllers;

use App\Models\LegalInfo;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StatusInfoController;

class LegalInfoController extends Controller
{
    // table name is: legal_infos
    protected $table = 'legal_infos';
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
     * Table name: legal_infos
     *
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $is_legal = $user->is_legal_person === 0 ? false : true;
        // $type = $user->type;
        // if ($type != 'supplier') {
        if ($is_legal) {
            return response()->json([
                'message' => 'شما مجاز به ثبت اطلاعات مالک کسب و کار نیستید.',
                'is_success' => false,
                'status_code' => 403,
            ]);
        }
        // Before create new record, we must check if the user has already filled the form or not
        $exists = $user->legal()->exists();
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
                'company_name' => 'required|max:128|string',
                // TODO: need change to enum
                // 'company_type' => 'required|max:128|alpha',
                'registration_number' => 'required|numeric|unique:legal_infos',
                'national_code' => 'required|numeric|unique:legal_infos',
                // 'economic_code' => 'required|numeric|digits:64|unique:legal_infos',
                'signatory' => 'required|max:128|string',
                // 'store_name' => 'nullable|max:128|alpha',
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
            $user->legal()->create(
                [
                    'company_name' => $data['company_name'],
                    // TODO: need change to enum
                    // 'company_type' => $data['company_type'],
                    'registration_number' => $data['registration_number'],
                    'national_code' => $data['national_code'],
                    // 'economic_code' => $data['economic_code'],
                    'signatory' => $data['signatory'],
                    // 'store_name' => array_key_exists('store_name' ,$data) ? $data['store_name'] : null,
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
     * Show the current user's legal info
     * Its for user seller/customer to show their info
     *
     */
    public function show(Request $request)
    {
        $user = $request->user();
        // check user status is there or not, auto check and create if not exists
        $this->checkUserStatus($request);
        $user_legal = $user->legal();
        if (!$user_legal->exists()) {
            return response()->json([
                'message' => 'اطلاعات مالک کسب و کار یافت نشد.',
                'status' => [],
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات مالک کسب و کار',
            'status' => $user->status()->where('table_name', $this->table)->get()->first(),
            'is_success' => true,
            'status_code' => 200,
            'data' => $user_legal->get()->first(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {

        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'company_name' => 'nullable|max:128|string',
            // TODO: need change to enum
            // 'company_type' => 'required|max:128|alpha',
            'registration_number' => 'nullable|numeric|unique:legal_infos',
            'national_code' => 'nullable|numeric|unique:legal_infos',
            // 'economic_code' => 'required|numeric|digits:64|unique:legal_infos',
            'signatory' => 'nullable|max:128|string',
            // 'store_name' => 'nullable|max:128|alpha',
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
        if ($user->legal()->exists() && count($data) > 0) {
            $legal_status = $user->status()->where('table_name', $this->table)->get()->first();
            if ($legal_status->is_approved && $legal_status->is_editable) {
                $legal = $user->legal()->get()->first();
                // update status table
                $legal_status->update(
                    [
                        "is_editable" => false,
                        "is_failed" => false,
                        "data" => json_encode([
                            'company_name' => array_key_exists('company_name', $data) ? $data['company_name'] : $legal->company_name,
                            // 'company_type' => array_key_exists('company_type', $data) ? $data['company_type'] : $legal->company_type,
                            'registration_number' => array_key_exists('registration_number', $data) ? $data['registration_number'] : $legal->registration_number,
                            'national_code' => array_key_exists('national_code', $data) ? $data['national_code'] : $legal->national_code,
                            // 'economic_code' => array_key_exists('economic_code', $data) ? $data['economic_code'] : $legal->economic_code,
                            'signatory' => array_key_exists('signatory', $data) ? $data['signatory'] : $legal->signatory,
                            // 'store_name' => array_key_exists('store_name', $data) ? $data['store_name'] : $legal->store_name,
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
    public function update(Request $request, LegalInfo $legalInfo)
    {
        // Todo : use this for update
        // $legal->update(
        // [
        //     'company_name' => array_key_exists('company_name', $data) ? $data['company_name'] : $legal->company_name,
        //    // 'company_type' => array_key_exists('company_type', $data) ? $data['company_type'] : $legal->company_type,
        //     'registration_number' => array_key_exists('registration_number', $data) ? $data['registration_number'] : $legal->registration_number,
        //     'national_code' => array_key_exists('national_code', $data) ? $data['national_code'] : $legal->national_code,
        //    // 'economic_code' => array_key_exists('economic_code', $data) ? $data['economic_code'] : $legal->economic_code,
        //     'signatory' => array_key_exists('signatory', $data) ? $data['signatory'] : $legal->signatory,
        //    // 'store_name' => array_key_exists('store_name', $data) ? $data['store_name'] : $legal->store_name,
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
            'company_name' => 'required|max:128|string',
            // TODO: need change to enum
            // 'company_type' => 'required|max:128|alpha',
            'registration_number' => 'required|numeric',
            'national_code' => 'required|numeric',
            // 'economic_code' => 'required|numeric|digits:64|unique:legal_infos',
            'signatory' => 'required|max:128|string',
            // 'store_name' => 'nullable|max:128|alpha',
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

        $legal = $user->legal();
        if($legal->exists()){
            $legal->get()->first()->update(
                [
                    'company_name' => $request->company_name,
                    'registration_number' => $request->registration_number,
                    'national_code' => $request->national_code,
                    'signatory' => $request->signatory,
                ]
            );
        }else{
            $legal->create(
                [
                    'company_name' => $request->company_name,
                    'registration_number' => $request->registration_number,
                    'national_code' => $request->national_code,
                    'signatory' => $request->signatory,
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
    public function destroy(LegalInfo $legalInfo)
    {
        //
    }
    private function checkUserStatus(Request $request): void
    {
        $status_controller = new StatusInfoController();
        $res = $status_controller->checkUserStatus($request);
    }
}
