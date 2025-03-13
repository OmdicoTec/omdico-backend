<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\v1\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

// php artisan make:controller Api/v1/AuthController
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:55|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }
        $data['password'] = bcrypt($request->password);
        $user = User::create($data);
        $accessToken = $user->createToken('UserToken')->accessToken;
        return response()->json([
            'user' => new UserResource($user),
            'token' => $accessToken,
            'token_type' => 'Bearer'
        ]);
    }
    public function register_2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:55', 'unique:users'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed']
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }
    
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
    
        $accessToken = $user->createToken('UserToken')->accessToken;
    
        return response()->json([
            'user' => new UserResource($user),
            'token' => $accessToken,
            'token_type' => 'Bearer'
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'email' => 'email|required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }
        if (!auth()->attempt($data)) {
            return response()->json('ایمیل یا رمز عبور اشتباه است.', 422);
        }
        $user = auth()->user();
        $tokenResult = $user->createToken('userToken');
        $tokenModel = $tokenResult->token;
        if ($request->remember_me)
            $tokenModel->expires_at = Carbon::now()->addWeeks(1);
        $tokenModel->save();
        return response()->json([
            'user' => new UserResource($user),
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer'
        ]);
    }
    public function logout(Request $request)
    {
        /** @var User $user
         */
        $request->user()->token()->revoke();
        return response()->json('شما با موفقیت خارج شدید.');
    }
    public function index(Request $request)
    {
        return response()->json(new UserResource($request->user()));
    }

    /**
     * Verification OTP for user registration and login
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric',
            'phone' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }
        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json('کاربری با این شماره تلفن یافت نشد.', 404);
        }
        if ($user->otp != $request->otp) {
            return response()->json('کد وارد شده صحیح نمی باشد.', 422);
        }
        $user->otp_verified_at = Carbon::now();
        $user->save();
        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken('UserToken')->accessToken,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Send OTP to user phone number for registration and login
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }
        $user = User::where('phone', $request->phone)->first();
        if ($user) {
            $user->otp = rand(1000, 9999);
            // Hint define Job to send sms
            $user->save();
            // send sms
            return response()->json(['messtage'=>'کد تایید برای شما ارسال شد.', 'code'=>$user->otp], 200);
        }
        $user = User::create([
            'phone' => $request->phone,
            'otp' => rand(1000, 9999),
        ]);
        // send sms
        return response()->json(['messtage'=>'کد تایید برای شما ارسال شد.', 'code'=>$user->otp], 200);
    }
}
