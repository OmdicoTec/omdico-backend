<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StatusInfoController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


/**
 * @group File management
 *
 * This class is used in users/admin controllers for uploading files.
 * Because we konw that the user can upload files in different parts of the application and we want to categorize them.
 *
 * APIs for managing files
 * @authenticated
 */
class FileController extends Controller
{

    // storage disk name : liara/arvan
    protected $disk = 'arvan';
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // get user files
        // $files = File::where('user_id', $request->user()->id)->get();
        $files = $request->user()->files;
        // return response
        return [
            'status' => true,
            'message' => 'User files',
            'res' => $files
        ];
    }

    /**
     * Service for getting temporary link with expiration date for a file uploaded by a specific user.
     *
     * @param $request is child of Request class.
     * @param $id is the ID of the file.
     *
     */
     public function getTemporaryLink($path = "temp/3_1692518517_7jalmlpEuTD5NReph8vC.jpg", $minutes = 60)
     {
         $expiration = now()->addMinutes($minutes); // set expiration date to 6 minutes from now
         $temporaryUrl = Storage::disk($this->disk)->temporaryUrl($path, $expiration);

         // for special ip address
        //  [
        //     'ResponseContentType' => 'application/octet-stream',
        //     'ResponseContentDisposition' => 'attachment; filename="' . $file->name . '"',
        //     'IpAddress' => $ip
        // ]
         return [
             'status' => true,
             'message' => 'Temporary link generated successfully',
             'res' => $temporaryUrl
         ];
     }
    /**
     * When the user uploads a file, the file is stored in the temporary folder or in S3 storage.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $dir = 'temp', $category = 'file', $alt = '', $type = 'image', $is_chunked = true)
    {
        // upload file
        $upload = $this->upload($request, $dir);

        // check upload status
        if ($upload['status']) {
            // store file info in database
            $file = File::create([
                'user_id' => $request->user()->id,
                'alt' => $alt,
                'category' => $category,
                'path' => $upload['res'],
                'type' => $type,
                'is_chunked' => $is_chunked,
            ]);

            // check file store status
            if ($file) {
                return [
                    'status' => true,
                    'message' => 'File uploaded successfully',
                    'res' => $file
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'File uploaded successfully but not stored in database',
                    'res' => null
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => 'File not uploaded',
                'res' => null
            ];
        }
    }

    /**
     * Store with specific name
     */
    public function storeWithName(Request $request, $dir = 'temp', $category = 'file', $alt = '', $type = 'image', $is_chunked = true)
    {
        // validate request
        $validator = Validator::make($request->all(), [
            // image file max size is 3 MB
            'file' => 'required|mimes:jpeg,png,jpg|max:3072|min:10',
        ]);
        // check validation
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'res' => null
            ];
        }

        // upload file
        $upload = $this->uploadWithName($request, $dir);

        // check upload status
        if ($upload['status']) {
            // store file info in database
            $file = File::create([
                'user_id' => $request->user()->id,
                'alt' => $alt,
                'category' => $category,
                'path' => $upload['res'],
                'type' => $type,
                'is_chunked' => $is_chunked,
            ]);

            // check file store status
            if ($file) {
                return [
                    'status' => true,
                    'message' => 'File uploaded successfully',
                    'res' => $file
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'File uploaded successfully but not stored in database',
                    'res' => null
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => 'File not uploaded',
                'res' => null
            ];
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        //
    }

    /**
     * Destroy temporary files
     * if time more than 24 hours and is_chunked is true
     */
    public function destroyTemporaryFiles()
    {
        // get files
        $files = File::where('is_chunked', true)->where('created_at', '<=', now()->subHours(24))->get();
        // delete files
        foreach ($files as $file) {
            Storage::disk($this->disk)->delete($file->path);
            $file->delete();
        }
        // return response
        return [
            'status' => true,
            'message' => 'Temporary files deleted successfully',
            'res' => null
        ];
    }
    /**
     * Service for uploading files to the server.
     * $data is child of Request class.
     * for example: $data = $request->file('national_card_image_back')
     */

    public function upload(Request $request, $dir)
    {
        try {
            $nationalCardImageBack = Storage::disk($this->disk)->put($dir, $request->file('file'), 'public');
            return [
                'status' => true,
                'message' => 'File uploaded successfully',
                'res' => $nationalCardImageBack
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'res' => null
            ];
        }
    }

    /**
     * Service for uploading files to the server.
     * This service is used for uploading file with specific name.
     *
     * @param $request is child of Request class.
     * @param $dir is the directory of the file.
     * @param $fileName is the name of the file.
     *
     */
    public function uploadWithName(Request $request, $dir = 'temp')
    {
        // template file name: user_id + epoch time + random string + file mime type
        $fileName = $request->user()->id . '_' . time() . '_' . Str::random(20) . '.' . $request->file('file')->getClientOriginalExtension();
        try {
            $file = Storage::disk($this->disk)->putFileAs($dir, $request->file('file'), $fileName);
            return [
                'status' => true,
                'message' => 'File uploaded successfully',
                'res' => $file
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'res' => null
            ];
        }
    }
}
