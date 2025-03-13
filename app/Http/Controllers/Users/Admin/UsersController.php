<?php

namespace App\Http\Controllers\Users\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StatusInfoController;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class UsersController extends Controller
{

    // pagination per page
    private $perPage = 10;
    /**
     * Display supplier list with pagination
     */
    public function supllierList()
    {
        // $users = User::where('type', 'supplier')->paginate($this->perPage)->toArray();
        $users = User::select('users.*', 'natural_infos.is_legal_person')
        ->where('type', 'supplier')
        ->leftJoin('natural_infos', 'users.id', '=', 'natural_infos.user_id')
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->toArray();
        return response()->json([
            'message' => 'لیست تامین کنندگان',
            'is_success' => true,
            'status_code' => 200,
            'data' => $users,
        ]);
    }

    /**
     * Supplier list for select option
     */
    public function supplierPlainList()
    {
        $users = User::select('id', 'name', 'family', 'type')->where('type', 'supplier')->get()->toArray();

        // concat name and family
        foreach ($users as $key => $value) {
            $users[$key]['name_family'] = $value['name'] . ' ' . $value['family'];
        }
        return response()->json([
            'message' => 'لیست تامین کنندگان',
            'is_success' => true,
            'status_code' => 200,
            'data' => $users,
        ]);
    }
    /**
     * Status of supplier
     */
    public function supplierStatus(int $id)
    {
        // Notice:
        // we dont use $user->status()->get()->toArray();
        // Because maybe we have incomplete status for user, and
        // method checkUserStatusFromAdmin() in StatusInfoController
        // can handle this problem
        $user = User::find($id);

        if ($user instanceof User && $user->type == 'supplier') {
            $statusInfo = new StatusInfoController();
            // return $statusInfo->checkUserStatusFromAdmin($user->id);
            return response()->json([
                'message' => 'وضعیت تامین کننده',
                'is_success' => true,
                'status_code' => 200,
                'data' => $statusInfo->checkUserStatusFromAdmin($user->id),
            ]);
        } else {
            return response()->json([
                'message' => 'کاربر یافت نشد',
                'is_success' => false,
                'status_code' => 404,
                'data' => [],
            ]);
        }
    }

    /**
     * Display customer list with pagination
     */
    public function customerList()
    {
        $users = User::where('type', 'customer')->orderBy('created_at', 'desc')->paginate($this->perPage)->toArray();
        return response()->json([
            'message' => 'لیست مشتریان',
            'is_success' => true,
            'status_code' => 200,
            'data' => $users,
        ]);
    }

    /**
     * Status of customer
     */
    public function customerStatus(int $id)
    {
        // Notice:
        // we dont use $user->status()->get()->toArray();
        // Because maybe we have incomplete status for user, and
        // method checkUserStatusFromAdmin() in StatusInfoController
        // can handle this problem
        $user = User::find($id);

        if ($user instanceof User && $user->type == 'customer') {
            $statusInfo = new StatusInfoController();
            // return $statusInfo->checkUserStatusFromAdmin($user->id);
            return response()->json([
                'message' => 'وضعیت مشتری',
                'is_success' => true,
                'status_code' => 200,
                'data' => $statusInfo->checkUserStatusFromAdmin($user->id),
            ]);
        } else {
            return response()->json([
                'message' => 'کاربر یافت نشد',
                'is_success' => false,
                'status_code' => 404,
                'data' => [],
            ]);
        }
    }

    /**
     * Add new user user with admin
     * get:
     * name
     * family
     * email not required
     * mobile_number
     * type
     */
    public function addUser(Request $request)
    {
        // validate data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users|max:255',
            'mobile_number' => 'required|string|unique:users|max:255',
            // type can be supplier or customer
            'type' => 'required|in:supplier,customer',
        ]);
        // if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'is_success' => false,
                'status_code' => 422,
                'data' => [],
            ]);
        }

        // carbon instance now
        $mobile_verified_at = now();

        // create user
        try
        {
            // try to create user
            $user = User::create([
                'name' => $request->name,
                'family' => $request->family,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'type' => $request->type,
                'mobile_verified_at' => $mobile_verified_at,
                // 'is_active' => true,
                // random string for password
                'password' => bcrypt(Str::random(12)),
            ]);
        }
        catch (\Exception $e)
        {

            // return error message when something went wrong
            return response()->json([
                'message' => ['خطایی در ثبت کاربر رخ داده است.'],
                'is_success' => false,
                'status_code' => 500,
                'data' => [],
            ]);
        }

        // if user created successfully
        if($user instanceof User){
            return response()->json([
                'message' => ['کاربر با موفقیت ثبت شد.'],
                'is_success' => true,
                'status_code' => 200,
                'data' => $user,
            ]);
        }
        else{
            // if user not created
            return response()->json([
                'message' => ['خطایی در ثبت کاربر رخ داده است.'],
                'is_success' => false,
                'status_code' => 500,
                'data' => [],
            ]);
        }
    }
}
