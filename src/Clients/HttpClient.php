<?php

namespace Fatkulnurk\BillerSdk\Clients;

use Fatkulnurk\BillerSdk\Config;

class HttpClient
{
    public static function request(): \Illuminate\Http\Client\PendingRequest
    {
        return \Illuminate\Support\Facades\Http::withoutVerifying()
            ->timeout(600)
            ->connectTimeout(600)
            ->withToken(Config::getInstance()->getToken());
    }
}