<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\v1\UserLogin;
use App\Http\Resources\v1\UserStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

// php artisan make:controller Api/v2/AuthController
class SmsController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api')->except(['register', 'verifyOTP', 'resendOTP']);
        // $this->request = $request;
    }
    public function enableToken(Request $request)
    {
        // if mobile already verified, return success response
        if ($request->user()->token()->hasVerifiedMobile()) {
            return response()->json([
                'user' => new UserStatus($request->user()),
                'message' => 'شماره موبایل شما قبلا تایید شده است.',
                'is_success' => false,
                'is_verified' => true,
                'renew' => false
            ]);
        }


        $validator = Validator::make($request->all(), [
            'code' => ['nullable', 'numeric', 'digits:6']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'کد وارد شده صحیح نمی باشد.',
                'is_success' => false,
                'errors' => $validator->errors(),
                'renew' => false
            ], 422);
        }

        // TODO: I think we not have this type of login
        // when user has not requested for a code yet...
        // if user even has not requested for a code yet or not registered snedMobileVerificationNotification for user create, send a code
        if ($request->user()->token()->mobile_verify_code_sent_at == null) {
            $request->user()->token()->sendMobileVerificationNotification(true);
            return response()->json([
                'message' => 'کد تایید به شماره موبایل شما ارسال شد.',
                'is_success' => true,
                'renew' => false
            ]);
        }
        // remove token if expired
        $secondsOfValidation = (int) config('mobile.seconds_of_validation');

        if ($secondsOfValidation > 0 &&  $request->user()->token()->mobile_verify_code_sent_at->diffInSeconds() > $secondsOfValidation) {
            // remove token if expired
            $request->user()->token()->delete();
            return response()->json([
                'message' => 'کد منقضی شده است. لطفا دوباره درخواست کد کنید.',
                'is_verified' => false,
                'is_success' => false,
                'renew' => true
            ], 422);
        }

        if ($request->code === auth()->user()->token()->mobile_verify_code && $request->code != "" && $request->user()->token()->mobile_attempts_left > 0) {
            $request->user()->markMobileAsVerified();
            // check if code is still valide
            $request->user()->token()->markMobileAsVerified();
            return response()->json([
                'user' => new UserStatus($request->user()),
                'message' => 'شماره موبایل شما با موفقیت تایید شد.',
                'is_verified' => true,
                'is_success' => true,
                'renew' => false
            ]);
        }

        // Max attempts feature, wrong otp code
        $maxAttempts = (int) config('mobile.max_attempts');
        if ($maxAttempts > 0) {

            // if user more than 2 times send wrong otp code
            if ($request->user()->token()->mobile_attempts_left <= 1) {
                if ($request->user()->token()->mobile_attempts_left == 1) {
                    $request->user()->token()->decrement('mobile_attempts_left');
                }

                // TODO: in generateToken (API Route) we must check user have active otp request
                //check how many seconds left to get unbanned after no more attempts left
                $seconds_left = (int) config('mobile.attempts_ban_seconds') - $request->user()->token()->mobile_last_attempt_date->diffInSeconds();
                if ($seconds_left > 0) {
                    return response()->json([
                        'message' => 'تعداد درخواست های شما بیش از حد مجاز است. لطفا ' . $seconds_left . ' ثانیه دیگر دوباره امتحان کنید.',
                        'seconds_left' => $seconds_left,
                        'is_success' => false,
                        'renew' => false
                    ], 422);
                }

                // remove token
                $request->user()->token()->delete();
                return response()->json([
                    'message' => 'کد باطل شده است، نیاز به ورود مجدد.',
                    'is_verified' => false,
                    'is_success' => false,
                    'renew' => true
                ], 422);
            }

            $request->user()->token()->decrement('mobile_attempts_left');
            $request->user()->token()->update(['mobile_last_attempt_date' => now()]);
            return response()->json([
                'message' => 'تعداد درخواست های شما بیش از حد مجاز است و یا کد وارد شده صحیح نمی باشد. لطفا دوباره امتحان کنید.',
                'attempts_left' => $request->user()->token()->mobile_attempts_left,
                'is_success' => false,
                'renew' => false
            ], 422);
        }

        return response()->json([
            'message' => 'کد وارد شده صحیح نمی باشد.',
            'is_success' => false,
            'renew' => false
        ], 422);
    }
    public function generateToken(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'mobile_number' => ['required', 'string', 'regex:/^[0-9]{11}$/', 'exists:users,mobile_number'],
            'remember_me' => ['boolean', 'nullable']
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده صحیح نیست.',
                'is_success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Not need check is user is existing, Because user mobile_number was checked in Validator
        $user = User::findByMobileNumber($data['mobile_number']);
        if (!$user instanceof User) {
            return response()->json(
                [
                    'message' => 'مشکلی پیش آمده است.',
                    'is_success' => false,
                ],
                422
            );
        }
        // createToken have a event
        $tokenResult = $user->createToken('userToken');
        // Need change in MustVerifyMobile trait
        $tokenModel = $tokenResult->token;
        // TODO: set token expire time in config file
        // before active token limit time is 10 minutes
        $tokenModel->expires_at = Carbon::now()->addWeeks(1);
        $tokenModel->save();
        return response()->json([
            'user_status' => new UserLogin($user),
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'is_success' => true,
        ])->header('Authorization', 'Bearer ' . $tokenResult->accessToken);;
    }
}
