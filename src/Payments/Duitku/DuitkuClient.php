<?php

namespace Fatkulnurk\BillerSdk\Payments\Duitku;

use Duitku\Config;

class DuitkuClient
{
    public static function config()
    {
        $duitkuConfig = new Config(
            config('setting.payments.duitku.merchant_key'),
            config('setting.payments.duitku.merchant_code')
        );
        $duitkuConfig->setSandboxMode(config('setting.payments.duitku.is_sandbox'));
        $duitkuConfig->setSanitizedMode(false);
        $duitkuConfig->setDuitkuLogs(false);

        return $duitkuConfig;
    }
}