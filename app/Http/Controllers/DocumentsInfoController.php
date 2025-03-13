<?php

namespace App\Http\Controllers;

use App\Models\DocumentsInfo;
use Illuminate\Http\Request;

use App\Models\File;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StatusInfoController;
use Illuminate\Support\Facades\Storage;

class DocumentsInfoController extends Controller
{
    // table name is: documents_infos
    protected $table = 'documents_infos';
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
     * Table name: documents_infos ->document
     *
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $type = $user->type;
        // Before create new record, we must check if the user has already filled the form or not
        $exists = $user->document()->exists();
        if ($exists) {
            // // remove first() and use exists() instead of it
            // $user_nature = $user->document()->delete();
            return response()->json([
                'message' => 'شما قبلا اطلاعات مدارک را ثبت کرده اید.',
                'is_success' => false,
                'is_already_filled' => true,
                'status_code' => 400,
            ]);
        } else {
            $data = $request->all();
            $validator = Validator::make($request->all(), [
                // input is image file
                'national_card_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'national_card_image_back' =>  'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Todo: API for storage image file
            $links = $this->uploadImage($request);

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
            $user->document()->create(
                [
                    'national_card_image' => $links['national_card_image'],
                    'national_card_image_back' => $links['national_card_image_back'],
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
                'message' => 'اطلاعات مدارک با موفقیت ثبت شد.',
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
        $user_nature = $user->document();
        if (!$user_nature->exists()) {
            return response()->json([
                'message' => 'اطلاعات مدارک یافت نشد.',
                'status' => [],
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات مدارک',
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
            'national_card_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'national_card_image_back' =>  'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
        // Todo: API for storage image file
        $links = $this->uploadImage($request);
        // get user
        $user = $request->user();
        // check user status is there or not
        if ($user->document()->exists() && count($links) > 0) {
            $document_status = $user->status()->where('table_name', $this->table)->get()->first();
            if ($document_status->is_approved && $document_status->is_editable) {
                $document = $user->document()->get()->first();
                // update status table
                $document_status->update(
                    [
                        "is_editable" => false,
                        "is_failed" => false,
                        "data" => json_encode([
                            'national_card_image' => array_key_exists('national_card_image', $links) ? $links['national_card_image'] : $document->national_card_image,
                            'national_card_image_back' => array_key_exists('national_card_image_back', $links) ? $links['national_card_image_back'] : $document->national_card_image_back,
                        ]),
                    ]
                );

                return response()->json([
                    'message' => 'اطلاعات مدارک با موفقیت ویرایش شد. اطلاعات شما در حال بررسی می باشد.',
                    'is_success' => true,
                    'status_code' => 200,
                ]);
            }
            return response()->json([
                'message' => 'امکان ویرایش اطلاعات مدارک وجود ندارد.',
                'is_success' => false,
                'status_code' => 400,
            ]);
        }
        return response()->json([
            'message' => 'اطلاعات مدارک یافت نشد.',
            'is_success' => false,
            'status_code' => 400,
        ]);
    }

    /**
     * Upload image file
     */
    public function uploadImage(Request $request)
    {
        $nationalCardImage = null;
        $nationalCardImageBack = null;
        if ($request->hasFile('national_card_image'))
            $nationalCardImage = Storage::disk(File::$disk)->put('images', $request->file('national_card_image'), 'private');
        if ($request->hasFile('national_card_image_back'))
            $nationalCardImageBack = Storage::disk(File::$disk)->put('images', $request->file('national_card_image_back'), 'private');
        // Todo: API for storage image file
        $links = [
            'national_card_image' => $nationalCardImage,
            'national_card_image_back' => $nationalCardImageBack,
        ];

        return $links;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentsInfo $documentsInfo)
    {
        // Todo : use this for update
        // $document->update(
        // [
        //     'national_card_image' => array_key_exists('national_card_image', $data) ? $data['national_card_image'] : $document->national_card_image,
        //     'national_card_image_back' => array_key_exists('national_card_image_back', $data) ? $data['national_card_image_back'] : $document->national_card_image_back,
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
            // input is image file
            'national_card_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'national_card_image_back' =>  'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Todo: API for storage image file
        $links = $this->uploadImage($request);

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors(),
                    'is_success' => false,
                    'status_code' => 422,
                ],
            );
        }

        $doc = $user->document();
        if ($doc->exists()) {
            $doc->get()->first()->update(
                [
                    'national_card_image' => $links['national_card_image'],
                    'national_card_image_back' => $links['national_card_image_back'],
                ]
            );
        } else {
            $doc->create(
                [
                    'national_card_image' => $links['national_card_image'],
                    'national_card_image_back' => $links['national_card_image_back'],
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
            'message' => 'اطلاعات مدارک با موفقیت ثبت شد.',
            'is_success' => true,
            'status_code' => 200,
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentsInfo $documentsInfo)
    {
        //
    }
    private function checkUserStatus(Request $request): void
    {
        $status_controller = new StatusInfoController();
        $res = $status_controller->checkUserStatus($request);
    }
}
