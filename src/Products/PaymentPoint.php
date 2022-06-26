<?php

namespace Fatkulnurk\BillerSdk\Products;

use Fatkulnurk\BillerSdk\Clients\HttpClient;
use Fatkulnurk\BillerSdk\ProductConfig;
use Fatkulnurk\BillerSdk\Enums\CustomerContactTypeEnum;

class PaymentPoint
{
    public function getPrepaids($withExpand = false)
    {
        $query = http_build_query([
            'expand' => (int)(($withExpand) ? 1 : 0)
        ]);

        $url = ProductConfig::getInstance()->getBaseUrl() . '/v1/products/payment-points/prepaids?' . $query;
        $response = HttpClient::request()->get($url);

        if ($response->ok()) {
            return collect($response->json()['data'])->toArray();
        }

        return [];
    }

    public function order(
        string $productId,
        string $target,
        string $customerContact = '',
        int    $ccType = CustomerContactTypeEnum::CC_TYPE_EMAIL,
        bool   $isCheck = false
    )
    {
        $url = ProductConfig::getInstance()->getBaseUrl() . '/v1/orders/payment-points';
        $response = HttpClient::request()->post($url, [
            'product_id' => $productId,
            'merchant_id' => ProductConfig::getInstance()->getMerchantId(),
            'target' => $target,
            'customer_contact' => $customerContact,
            'cc_type' => $ccType,
            'is_check' => $isCheck,
        ]);

        if ($response->ok()) {
            return collect($response->json()['data'])->toArray();
        }

        return optional($response->json())['message'] ?? $response->body();
    }

    public function getTransactions(string $reffId = '')
    {
        $url = ProductConfig::getInstance()->getBaseUrl() . '/v1/transactions/payment-points';

        if (!blank($reffId)) {
            $url = $url . '/' . $reffId;
        }

        $response = HttpClient::request()->post($url);

        if ($response->ok()) {
            return collect($response->json()['data'])->toArray();
        }

        return optional($response->json())['message'] ?? $response->body();
    }

    public function checkTransaction(string $reffId)
    {
        return $this->getTransactions($reffId);
    }
}