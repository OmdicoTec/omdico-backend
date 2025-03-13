<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\v1\UserStatus;

class VerifyMobileController extends Controller
{
    /**
     * mobile_post
     */
    public function __invoke(Request $request)
    {
        // if (!$request->user()->hasVerifiedMobile()) {
        //     return $this->enableToken($request);
        // }
        // if mobile already verified, return success response
        if ($request->user()->hasVerifiedMobile()) {
            return response()->json([
                'message' => 'شماره موبایل شما قبلا تایید شده است.'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'code' => ['nullable', 'numeric', 'digits:6']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'کد وارد شده صحیح نمی باشد.',
                'errors' => $validator->errors()
            ], 422);
        }

        // when user has not requested for a code yet...
        // if user even has not requested for a code yet or not registered snedMobileVerificationNotification for user create, send a code
        if($request->user()->mobile_verify_code_sent_at == null){
            $request->user()->sendMobileVerificationNotification(true);
            return response()->json([
                'message' => 'کد تایید به شماره موبایل شما ارسال شد.'
            ]);
        }
        if ($request->code === auth()->user()->mobile_verify_code && $request->code != "" && $request->user()->mobile_attempts_left > 0) {
            // check if code is still valide
            $secondsOfValidation = (int) config('mobile.seconds_of_validation');
            if ($secondsOfValidation > 0 &&  $request->user()->mobile_verify_code_sent_at->diffInSeconds() > $secondsOfValidation) {
                return response()->json([
                    'message' => 'کد منقضی شده است. لطفا دوباره امتحان کنید.'
                ], 422);
            } else {
                $request->user()->markMobileAsVerified();
                // when user first time verify mobile so we should mark token as verified
                $request->user()->token()->markMobileAsVerified();
                return response()->json([
                    'message' => 'شماره موبایل شما با موفقیت تایید شد.'
                ]);
            }
        }

        // Max attempts feature
        $maxAttempts = (int) config('mobile.max_attempts');
        if ($maxAttempts > 0) {
            if ($request->user()->mobile_attempts_left <= 1){
                if ($request->user()->mobile_attempts_left == 1) {
                    $request->user()->decrement('mobile_attempts_left');
                }

                //check how many seconds left to get unbanned after no more attempts left
                $seconds_left = (int) config('mobile.attempts_ban_seconds') - $request->user()->mobile_last_attempt_date->diffInSeconds();
                if ($seconds_left > 0){
                    return response()->json([
                        'message' => 'تعداد درخواست های شما بیش از حد مجاز است. لطفا ' . $seconds_left . ' ثانیه دیگر دوباره امتحان کنید.',
                        'seconds_left' => $seconds_left,
                    ], 422);
                }

                // Send new code and set new attempts when user is no longer banned
                $request->user()->sendMobileVerificationNotification(true);
                return response()->json([
                    'message' => 'کد مجددا برای شما ارسال شد. لطفا دوباره امتحان کنید.',
                ], 422);
            }

            $request->user()->decrement('mobile_attempts_left');
            $request->user()->update(['mobile_last_attempt_date' => now()]);
            return response()->json([
                'message' => 'تعداد درخواست های شما بیش از حد مجاز است. لطفا دوباره امتحان کنید.',
                'attempts_left' => $request->user()->mobile_attempts_left
            ], 422);
        }

        return response()->json([
            'message' => 'کد وارد شده صحیح نمی باشد.'
        ], 422);
    }

    public function enableToken(Request $request)
    {
        // if mobile already verified, return success response
        if ($request->user()->token()->hasVerifiedMobile()) {
            return response()->json([
                'user' => new UserStatus($request->user()),
                'message' => 'شماره موبایل شما قبلا تایید شده است.',
                'is_success' => false,
                'is_verified' => true
            ]);
        }


        $validator = Validator::make($request->all(), [
            'code' => ['nullable', 'numeric', 'digits:6']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'کد وارد شده صحیح نمی باشد.',
                'is_success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // when user has not requested for a code yet...
        // if user even has not requested for a code yet or not registered snedMobileVerificationNotification for user create, send a code
        if ($request->user()->token()->mobile_verify_code_sent_at == null) {
            $request->user()->token()->sendMobileVerificationNotification(true);
            return response()->json([
                'message' => 'کد تایید به شماره موبایل شما ارسال شد.',
                'is_success' => true
            ]);
        }
        if ($request->code === auth()->user()->token()->mobile_verify_code && $request->code != "" && $request->user()->token()->mobile_attempts_left > 0) {
            // check if code is still valide
            $secondsOfValidation = (int) config('mobile.seconds_of_validation');
            if ($secondsOfValidation > 0 &&  $request->user()->token()->mobile_verify_code_sent_at->diffInSeconds() > $secondsOfValidation) {
                return response()->json([
                    'message' => 'کد منقضی شده است. لطفا دوباره درخواست کد کنید.',
                    'is_verified' => false,
                    'is_success' => false
                ], 422);
            } else {
                $request->user()->token()->markMobileAsVerified();
                return response()->json([
                    'user' => new UserStatus($request->user()),
                    'message' => 'شماره موبایل شما با موفقیت تایید شد.',
                    'is_verified' => true,
                    'is_success' => true
                ]);
            }
        }

        // Max attempts feature
        $maxAttempts = (int) config('mobile.max_attempts');
        if ($maxAttempts > 0) {
            if ($request->user()->token()->mobile_attempts_left <= 1) {
                if ($request->user()->token()->mobile_attempts_left == 1) {
                    $request->user()->token()->decrement('mobile_attempts_left');
                }

                //check how many seconds left to get unbanned after no more attempts left
                $seconds_left = (int) config('mobile.attempts_ban_seconds') - $request->user()->token()->mobile_last_attempt_date->diffInSeconds();
                if ($seconds_left > 0) {
                    return response()->json([
                        'message' => 'تعداد درخواست های شما بیش از حد مجاز است. لطفا ' . $seconds_left . ' ثانیه دیگر دوباره امتحان کنید.',
                        'seconds_left' => $seconds_left,
                        'is_success' => false,
                    ], 422);
                }

                // Send new code and set new attempts when user is no longer banned
                $request->user()->token()->sendMobileVerificationNotification(true);
                return response()->json([
                    'message' => 'کد مجددا برای شما ارسال شد. لطفا دوباره امتحان کنید.',
                    'is_success' => true,
                ], 422);
            }

            $request->user()->token()->decrement('mobile_attempts_left');
            $request->user()->token()->update(['mobile_last_attempt_date' => now()]);
            return response()->json([
                'message' => 'تعداد درخواست های شما بیش از حد مجاز است و یا کد وارد شده صحیح نمی باشد. لطفا دوباره امتحان کنید.',
                'attempts_left' => $request->user()->token()->mobile_attempts_left,
                'is_success' => false,
            ], 422);
        }

        return response()->json([
            'message' => 'کد وارد شده صحیح نمی باشد.',
            'is_success' => false,
        ], 422);
    }
}
