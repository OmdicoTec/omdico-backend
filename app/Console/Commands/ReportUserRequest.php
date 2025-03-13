<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendSms;
use App\Models\ScheduleReport;
use App\Models\purchase_requests;

class ReportUserRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:userreqcount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report the number of users request each hour to admin (09104140122,09380918609)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $res = $this->checkLastRequestPending();

        if ($res['changed']) {
            $tokens = [
                'token' => 'یک',
                'token_two' => $res['count']
            ];
            $requestShape = 'token={token}&token2={token_two}';
            $to_milad = '09380918609';
            $to_pariya = '09104140122';

            SendSms::dispatch($to_milad, $tokens, $requestShape, 'adminhavenewrequest');
            SendSms::dispatch($to_pariya, $tokens, $requestShape, 'adminhavenewrequest');
        }

        return [
            "return" => [
                "is_send" => $res['changed'],
                "status" => 200,
                "message" => "report is send.",
            ]
        ];
    }

    private function checkLastRequestPending()
    {
        // Find or create a ScheduleReport with the specified name
        $scheduleReport = ScheduleReport::firstOrCreate([
            'name' => 'userreqcount',
        ]);

        $oldInformation = $scheduleReport->information;

        $lastId = 0;
        if (is_array($oldInformation) && array_key_exists('last_purchase_request_id', $oldInformation)) {
            $lastId = $scheduleReport->information;
            $lastId = $lastId['last_purchase_request_id'];
        }

        // Get the last purchase_requests model (assuming it has an 'id')
        $lastPurchaseRequest = purchase_requests::latest('id')->first();

        // Update the information field
        $scheduleReport->information = [
            'last_purchase_request_id' => $lastPurchaseRequest->id,
        ];
        $scheduleReport->save();
        $countPending = purchase_requests::whereIn('status', ['supplierpending', 'pending'])->count();

        return [
            'changed' => intval($lastId) != intval($lastPurchaseRequest->id),
            'count' => $countPending,
        ];
    }
}
