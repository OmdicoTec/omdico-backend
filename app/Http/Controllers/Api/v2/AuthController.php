<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\v1\UserLogin;
use App\Http\Resources\v1\UserStatus;
use App\Models\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

// php artisan make:controller Api/v2/AuthController
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            // 'name' => 'required|max:55|alpha|regex:/^[\p{Arabic}\s]+$/u',
            // 'password' => 'required|confirmed',
            'name' => 'required|max:55|string',
            'family' => 'required|max:55|string',
            'type' => 'required|in:supplier,customer',
            'is_legal_person' => 'required|in:legal,real',
            'mobile_number' => 'required|numeric|unique:users',
        ]);
        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()],
                422
            );
        }
        $data['password'] = bcrypt(Str::random(40));
        $data['is_legal_person'] = $data['is_legal_person'] === 'legal' ? true : false;
        $user = User::create($data);
        $accessToken = $user->createToken('UserToken')->accessToken;
        return response()->json([
            'user_status' => new UserLogin($user),
            'token' => $accessToken,
            'token_type' => 'Bearer'
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json('شما با موفقیت خارج شدید.');
    }
    public function index(Request $request)
    {
        // show user active session laravel/passport:
        // $request->user()->token()->scopes = ['*'];
        return response()->json([
            'user' => new UserStatus($request->user()),
            'message' => 'اطلاعات کاربر',
            'is_verified' => true,
            'is_success' => true
            ]);
    }

    /**
     * Resend the mobile verification notification.
     * @param Request $request
     */
    public function resendOTP(Request $request)
    {
        if ($request->user()->hasVerifiedMobile()) {
            return response()->json('شما قبلا شماره موبایل خود را تایید کرده اید.', 422);
        }
        $request->user()->sendMobileVerificationNotification(true);
        return response()->json(["message" => 'پیامک تایید شماره موبایل برای شما ارسال شد.'], 200);
    }

    /**
     * Test new relationship
     */
    public function test()
    {
        $user = user::find(1);
        // $user->natural()->create([
        //     'user_id' => $user->id,
        //     'first_name' => 'milad',
        //     'last_name' => 'mohammadi',
        //     'national_code' => '1234567890',
        //     'mobile_number' => '09123456789',
        //     'other_mobile_number' => '09123456789',
        //     'is_legal_person' => false,
        // ]);
        // $user->status()->create([
        //     'user_id' => $user->id,
        //     'is_approved' => false,
        //     'is_editable' => true,
        //     'is_failed' => false,
        //     'note' => 'test',
        //     'data' => 'test',
        // ]);
        // dd($user->status()->get());
        dd($user->natural->get()->first()->toArray());
        return response()->json($user);
    }
}
