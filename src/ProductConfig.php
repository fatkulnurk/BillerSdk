<?php
namespace Fatkulnurk\BillerSdk;

use Fatkulnurk\BillerSdk\Traits\Singleton;

class ProductConfig
{
    use Singleton;

    private string $baseUrl = 'http://bayarcepat.test/api';

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return config('setting.api.base_url', $this->baseUrl);
    }

    public function setBaseUrl(string $baseUrl = '*/api')
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return config('setting.api.token');
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return config('setting.api.merchant_id');
    }
}