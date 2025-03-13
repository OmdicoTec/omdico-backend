<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// At the top of the file.
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;

class PayController extends Controller
{
    //
    public function pay()
    {
        // Create new invoice.
        $invoice = new Invoice;
        // Set invoice amount.
        $invoice->amount(1000);
        // $uuid = $invoice->getUuid();
        // $amount = $invoice->getAmount();
        // $invoice->via('local');
        // $via = $invoice->getDriver();
        //transactionId and get transactionId
        $invoice->detail('user_id', '1');
        // Purchase the given invoice.
        $res =  Payment::purchase($invoice, function ($driver, $transactionId) {
        })->pay();
        dd($invoice, $res);
        return response()->json($res);
    }

    // callback url
    public function callback(Request $request)
    {
        $transactionId = $request->get('trackId');
        $orderId = $request->get('orderId');
        $status = $request->get('status');
        $success = $request->get('success');

        // dd($transactionId);
        // get transactionId from database & user_id
        // dd($transactionId);
        return $this->verify($transactionId);
        /**
            {
            "status": "success",
            "is_verified": true,
            "is_success": true,
            "message": "پرداخت با موفقیت انجام شده است.",
            "ReferenceId": "",
            "driver": "Zibal",
            "date": "2024-07-21T12:56:23.608625Z"
            }
            {
            "status": "error",
            "code": 201,
            "is_verified": false,
            "is_success": false,
            "message": "previously verifed"
            }
         */
    }

    // verify url
    private function verify($transactionId)
    {

        // InvoicePurchasedEvent: هنگامی که یک پرداخت به درستی ثبت شود این رویداد اتفاق می‌افتد.
        // InvoiceVerifiedEvent: هنگامی که یک پرداخت به درستی وریفای شود این رویداد اتفاق می‌افتد
        // You need to verify the payment to ensure the invoice has been paid successfully.
        // We use transaction id to verify payments
        // It is a good practice to add invoice amount as well.
        try {
            $receipt = Payment::amount(1000)->transactionId($transactionId)->verify();
            // You can show payment referenceId to the user
            return response()->json([
                'status' => 'success',
                'is_verified' => true,
                'is_success' => true,
                'message' => 'پرداخت با موفقیت انجام شده است.',
                'ReferenceId' => $receipt->getReferenceId(),
                'driver' => $receipt->getDriver(),
                'date' => $receipt->getDate(),
            ]);

            // status	"success"
            // is_verified	true
            // is_success	true
            // message	"Payment was successful."
            // ReferenceId	"1312544"
            // driver	"idpay"
            // date	"2023-11-07T10:36:08.049666Z"
        } catch (InvalidPaymentException $exception) {
            dd($exception);
            /**
             * when payment is not verified, it will throw an exception.
             * getMessage method, returns a suitable message that can be used in user interface.
             */
            // return json response
            return response()->json([
                'status' => 'error',
                'code' => $exception->getCode(),
                'is_verified' => $exception->getCode() === 101 ? true:false,
                'is_success' => false,
                'message' => $exception->getMessage(),
            ]);
            // 101 : "پرداخت قبلا تایید شده است."
            // 53 : "تایید پرداخت امکان پذیر نیست."
        }
    }
}
