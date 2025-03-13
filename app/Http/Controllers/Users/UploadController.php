<?php

namespace App\Http\Controllers\Users;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use App\Models\User;

class UploadController extends FileController
{
    /**
     * get image from request and upload it to storage
     * use store function in FileController
     *
     */
    public function uploadImage(Request $request)
    {
        $is_chunked = $request->user()->type === "admin" ? false: true;
        $validator = Validator::make($request->all(), [
            // image file max size is 3 MB
            'file' => 'required|mimes:jpeg,png,jpg,webp|max:3072|min:1',
        ]);
        // dd(count($request->file()));
        // check validation
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'res' => null
            ];
        }

        return $this->store($request, 'image', 'products', 'image', 'image', $is_chunked);
    }

    /**
     * List of special user images, Admin access need
     */
    public function getUserImagesById(Request $request, User $id)
    {
        $images = $id->files()->get()->toArray();
        return response()->json([
            'message' => 'لیست تصاویر کاربر',
            'is_success' => true,
            'status_code' => 200,
            'data' => $images,
        ]);
    }

}
