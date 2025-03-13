<?php

namespace App\Http\Controllers;

use App\Models\FinanceInfo;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StatusInfoController;

class FinanceInfoController extends Controller
{
    // table name is: finance_infos
    protected $table = 'finance_infos';
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
     * Table name: finance_infos
     *
     */
    public function store(Request $request)
    {

        $user = $request->user();
        $type = $user->type;
        // Before create new record, we must check if the user has already filled the form or not
        $exists = $user->finance()->exists();
        if ($exists) {
            return response()->json([
                'message' => 'شما قبلا اطلاعات مالی را ثبت کرده اید.',
                'is_success' => false,
                'is_already_filled' => true,
                'status_code' => 400,
            ]);
        } else {
            $data = $request->all();
            $validator = Validator::make($request->all(), [
                'card_number' => 'nullable|numeric|digits:16|unique:finance_infos',
                // Iranian shaba number regex validation: ^(?:IR)(?=.{24}$)[0-9]*$
                'shaba_number' => 'required|regex:/^(?:IR)(?=.{24}$)[0-9]*$/|unique:finance_infos'
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
            $user->finance()->create(
                [
                    'user_id' => $user->id,
                    'card_number' => $data['card_number'],
                    'shaba_number' => $data['shaba_number'],
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
                'message' => 'اطلاعات مالی با موفقیت ثبت شد.',
                'is_success' => true,
                'status_code' => 200,
            ]);
        }
    }

    /**
     * Show the current user's finance info
     * Its for user seller/customer to show their info
     *
     */
    public function show(Request $request)
    {
        $user = $request->user();
        // check user status is there or not, auto check and create if not exists
        $this->checkUserStatus($request);
        $user_finance = $user->finance();
        if (!$user_finance->exists()) {
            return response()->json([
                'message' => 'اطلاعات مالی یافت نشد.',
                'status' => [],
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات مالی',
            'status' => $user->status()->where('table_name', $this->table)->get()->first(),
            'is_success' => true,
            'status_code' => 200,
            'data' => $user_finance->get()->first(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {

        $data = $request->all();
        // get user
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'card_number' => 'nullable|numeric|digits:16|unique:finance_infos,card_number,' . $user->id . ',user_id',
            // Iranian shaba number regex validation: ^(?:IR)(?=.{24}$)[0-9]*$
            'shaba_number' => 'nullable|regex:/^(?:IR)(?=.{24}$)[0-9]*$/|unique:finance_infos,shaba_number,' . $user->id . ',user_id'
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

        // check user status is there or not
        if ($user->finance()->exists() && count($data) > 0) {
            $finance_status = $user->status()->where('table_name', $this->table)->get()->first();
            if ($finance_status->is_approved && $finance_status->is_editable) {
                $finance = $user->finance()->get()->first();
                // update status table
                $finance_status->update(
                    [
                        "is_editable" => false,
                        "is_failed" => false,
                        "data" => json_encode([
                            'card_number' => array_key_exists('card_number', $data) ? $data['card_number'] : $finance->card_number,
                            'shaba_number' => array_key_exists('shaba_number', $data) ? $data['shaba_number'] : $finance->shaba_number,
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
    public function update(Request $request, FinanceInfo $financeInfo)
    {
        // Todo : use this for update
        // $finance->update(
        // [
        //     'card_number' => array_key_exists('card_number', $data) ? $data['card_number'] : $finance->card_number,
        //     'shaba_number' => array_key_exists('shaba_number', $data) ? $data['shaba_number'] : $finance->shaba_number,
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
            'card_number' => 'nullable|numeric|digits:16|unique:finance_infos,card_number,' . $user->id . ',user_id',
            // Iranian shaba number regex validation: ^(?:IR)(?=.{24}$)[0-9]*$
            'shaba_number' => 'required|regex:/^(?=.{24}$)[0-9]*$/|unique:finance_infos,shaba_number,' . $user->id . ',user_id',
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

        $finance = $user->finance();
        if ($finance->exists()) {
            $finance->get()->first()->update(
                [
                    'card_number' => $request->card_number,
                    'shaba_number' => $request->shaba_number,
                ]
            );
        } else {
            $finance->create(
                [
                    'user_id' => $user->id,
                    'card_number' => $request->card_number,
                    'shaba_number' => $request->shaba_number,
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
            'message' => 'اطلاعات مالی با موفقیت ویرایش شد.',
            'is_success' => true,
            'status_code' => 200,
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinanceInfo $financeInfo)
    {
        //
    }

    private function checkUserStatus(Request $request): void
    {
        $status_controller = new StatusInfoController();
        $res = $status_controller->checkUserStatus($request);
    }
}
