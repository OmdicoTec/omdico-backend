<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $params = [];
    protected string $uri = '';
    public $tries = 2; // Set the maximum number of retries
    public $retryAfter = 10; // Set the delay between retrie
    public $deleteWhenMissingModels = true; // Automatically delete the job when a model is missing

    /**
     * Create a new job instance.
     * @param string $recipient
     * @param array $tokens
     * @param string $requestShape
     * @param string $template
     *
     * @return void
     */
    public function __construct(string $recipient, array $tokens, string $requestShape, string $template = 'verify')
    {
        $this->params = [
            'endpoint' => 'https://api.kavenegar.com/v1/656C512F4D4A59515A4D6F334F52586A48345955712F4E58795958725567546936616D6678746F667643383D/verify/lookup.json?',
            'receptor' => $recipient,
        ];
        $this->params += $tokens;

        # $requestShape depend on tokens array and refered to template name on kavenegar panel.
        $this->uri = "{+endpoint}receptor={receptor}&" . $requestShape . "&template=" . $template;
    }

    /**
     * Run sms Job in queue.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // Make an HTTP request to the SMS API
            $response = Http::withUrlParameters($this->params)->get($this->uri);
            if (!$response->successful()) {
                // Log the error response
                Log::error('SMS API response error', ['response' => $response->body()]);
                throw new \Exception('Failed to send SMS');
            }
            // $response->successful()
        } catch (\Throwable $exception) {
            // The job will automatically retry after 15 seconds if it fails
            if ($this->attempts() < $this->tries) {
                $this->release($this->retryAfter);
            } else {
                // Handle the case when max retries have been reached
                // If you want to prevent the job from going to the failed_jobs table, delete it
                $this->delete();
            }
        }
    }
}
