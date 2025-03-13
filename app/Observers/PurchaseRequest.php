<?php

namespace App\Observers;

use App\Models\purchase_requests;
use App\Models\User;
use App\Models\category;
use App\Jobs\SendSms;

// use App\Http\Controllers\PurchaseRequestsController;

class PurchaseRequest
{
    /**
     * Handle the purchase_requests "created" event.
     * It's only worked on status column and when it set pending or supplierpending
     *
     */
    public function created(purchase_requests $purchase_requests): void
    {
        $state = $purchase_requests->status;
        if ($state === 'pending' || $state === 'supplierpending') {
            $phone = $purchase_requests->user->mobile_number;
            $token = [
                'token' => $purchase_requests->id
            ];

            $requestShape = 'token={token}';
            SendSms::dispatch($phone, $token, $requestShape, 'createrequest');
        }
    }

    /**
     * Handle the purchase_requests "updated" event.
     *
     * It's only worked on status column!
     */
    public function updated(purchase_requests $purchase_requests): void
    {
        if ($purchase_requests->isDirty('status')) {
            // It's handle switch for each steps of purchase requests
            $state = $purchase_requests->status;
            // check it's from shop or not, if from shop have a product_id
            $from = $purchase_requests->product_id;

            switch ($state) {
                case 'active':
                    $this->statusActive($purchase_requests, $from);
                    break;
                default:
                    // var_dump('break');
                    break;
            }
        }
    }

    /**
     * When create a new request from shop and panel
     */
    private function statusActive(purchase_requests $purchase_requests, $from)
    {
        $token = [
            'token' => $purchase_requests->id
        ];

        $requestShape = 'token={token}';

        if ($purchase_requests->supplier()->first()){
            // send message to special supplier
            SendSms::dispatch($purchase_requests->supplier()->first()->mobile_number, $token, $requestShape, 'haveshoporder');
        } else{
            // send message to all supplier have special category
            # check user is supplier or not
            $self_mobile = $purchase_requests->user->mobile_number;
            $recipients = category::find($purchase_requests->category_id)->supplierList(['id', 'mobile_number'])->get()->pluck('mobile_number')->all();

            // Find the index of the number in the array
            $index = array_search($self_mobile, $recipients);

            if ($index !== false) {
                // Remove the number from the array
                unset($recipients[$index]);

                // Re-index the array (optional, if you want continuous integer keys)
                $recipients = array_values($recipients);
            }

            foreach ($recipients as $recipient) {
                SendSms::dispatch($recipient, $token, $requestShape, 'haveshoporder');
            }
        }

        // send message to buyer
        SendSms::dispatch($purchase_requests->user->mobile_number, $token, $requestShape, 'preinvoicing');
    }

    /**
     * Handle the purchase_requests "deleted" event.
     */
    public function deleted(purchase_requests $purchase_requests): void
    {
        //
    }

    /**
     * Handle the purchase_requests "restored" event.
     */
    public function restored(purchase_requests $purchase_requests): void
    {
        //
    }

    /**
     * Handle the purchase_requests "force deleted" event.
     */
    public function forceDeleted(purchase_requests $purchase_requests): void
    {
        //
    }
}
