<?php

namespace App\Http\Controllers;

use App\Models\ContractInfo;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StatusInfoController;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\File;

class ContractInfoController extends Controller
{
    // table name is: contract_infos
    protected $table = 'contract_infos';
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
     * Table name: contract_infos
     *
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $type = $user->type;
        // Before create new record, we must check if the user has already filled the form or not
        $exists = $user->contract()->exists();
        if ($exists) {
            return response()->json([
                'message' => 'شما قبلا اطلاعات قرارداد را ثبت کرده اید.',
                'is_success' => false,
                'is_already_filled' => true,
                'status_code' => 400,
            ]);
        } else {
            $data = $request->all();
            $validator = Validator::make($request->all(), [
                // maybe need multiple image
                'contract_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
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

            $links = $this->uploadImage($request);
            // array_key_exists
            $user->contract()->create(
                [
                    'user_id' => $user->id,
                    // maybe need multiple image
                    'contract_image' => $links['contract_image'],
                    // validate date, date is epoch time
                    'start_date' => Carbon::now()->timestamp,
                    // next year
                    'end_date' => Carbon::now()->addYear()->timestamp,
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
                'message' => 'اطلاعات قرارداد با موفقیت ثبت شد.',
                'is_success' => true,
                'status_code' => 200,
            ]);
        }
    }

    /**
     * Show the current user's contract info
     * Its for user seller/customer to show their info
     *
     */
    public function show(Request $request)
    {
        $user = $request->user();
        // check user status is there or not, auto check and create if not exists
        $this->checkUserStatus($request);
        $user_nature = $user->contract();
        if (!$user_nature->exists()) {
            return response()->json([
                'message' => 'اطلاعات قرارداد یافت نشد.',
                'status' => [],
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات قرارداد',
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
            // maybe need multiple image
            'contract_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
        $links = $this->uploadImage($request);
        // check user status is there or not
        // Todo: check count is not enough beacuse we have image
        if ($user->contract()->exists() && count($data) > 0) {
            $contract_status = $user->status()->where('table_name', $this->table)->get()->first();
            if ($contract_status->is_approved && $contract_status->is_editable) {
                $contract = $user->contract()->get()->first();
                // update status table
                $contract_status->update(
                    [
                        "is_editable" => false,
                        "is_failed" => false,
                        "data" => json_encode([
                            'contract_image' => array_key_exists('contract_image', $links) ? $links['contract_image'] : $contract->contract_image,
                            'start_date' => Carbon::now()->timestamp,
                            // next year
                            'end_date' => Carbon::now()->addYear()->timestamp,
                        ]),
                    ]
                );

                return response()->json([
                    'message' => 'اطلاعات قرارداد با موفقیت ویرایش شد. اطلاعات شما در حال بررسی می باشد.',
                    'is_success' => true,
                    'status_code' => 200,
                ]);
            }
            return response()->json([
                'message' => 'امکان ویرایش اطلاعات قرارداد وجود ندارد.',
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات قرارداد یافت نشد.',
            'is_success' => false,
            'status_code' => 400,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContractInfo $contractInfo)
    {
        //
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
            // maybe need multiple image
            'contract_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
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

        $links = $this->uploadImage($request);

        $contract = $user->contract();
        if ($contract->exists()) {
            $contract->update(
                [
                    'contract_image' => $links['contract_image'],
                    'start_date' => Carbon::now()->timestamp,
                    // next year
                    'end_date' => Carbon::now()->addYear()->timestamp,
                ]
            );
        } else {
            $contract->create(
                [
                    'user_id' => $user->id,
                    // maybe need multiple image
                    'contract_image' => $links['contract_image'],
                    // validate date, date is epoch time
                    'start_date' => Carbon::now()->timestamp,
                    // next year
                    'end_date' => Carbon::now()->addYear()->timestamp,
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
            'message' => 'اطلاعات قرارداد با موفقیت ویرایش شد.',
            'is_success' => true,
            'status_code' => 200,
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContractInfo $contractInfo)
    {
        //
    }

    /**
     * Uploads an image from the request.
     *
     * @param Request $request
     * @return array
     */
    public function uploadImage(Request $request)
    {
        $contract_image = null; // Initialize contract image variable
        $nationalCardImageBack = null; // Initialize national card image back variable
        if ($request->hasFile('contract_image')) {
            $contract_image = Storage::disk(File::$disk)->put('images', $request->file('contract_image'), 'private'); // Store the contract image
        }
        $links = [
            'contract_image' => $contract_image, // Add contract image to links array
        ];

        return $links; // Return the links array
    }
    private function checkUserStatus(Request $request): void
    {
        $status_controller = new StatusInfoController();
        $res = $status_controller->checkUserStatus($request);
    }
}
