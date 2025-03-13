<?php

namespace App\Message;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use App\Jobs\SendSms;

class SmsMessage
{

    protected string $user;
    protected string $password;
    protected string $to;
    protected string $from;
    protected string $baseUrl;
    protected array $lines;
    protected string $dryrun = 'no';

    /**
     * SmsMessage constructor.
     * @param array $lines
     */
    public function __construct($lines = [])
    {
        $this->lines = $lines;

        // Pull in config from the config/services.php file.
        $this->from = 'services.elks.from';
        $this->baseUrl = 'https://api.kavenegar.com/v1/656C512F4D4A59515A4D6F334F52586A48345955712F4E58795958725567546936616D6678746F667643383D/verify/lookup.json?';
        $this->user = 'services.elks.user';
        $this->password = 'services.elks.password';
    }

    public function line($line = ''): self
    {
        $this->lines[] = $line;

        return $this;
    }

    public function to($to): self
    {
        $this->to = $to;

        return $this;
    }

    public function from($from): self
    {
        $this->from = $from;

        return $this;
    }

    public function send(): mixed
    {
        if (!$this->from || !$this->to || !count($this->lines)) {
            throw new \Exception('SMS not correct.');
        }

        $tokens = [
            'token' => $this->lines
        ];
        $requestShape = 'token={token}';
        SendSms::dispatch($this->to, $tokens, $requestShape, 'verify');

        return [
            "return" => [
                "status" => 200,
                "message" => "ارسال شد",
            ]
        ];
    }

    public function dryrun($dry = 'yes'): self
    {
        $this->dryrun = $dry;
        return $this;
    }
}
