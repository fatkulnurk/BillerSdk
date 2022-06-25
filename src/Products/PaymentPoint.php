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
        int    $ccType = CustomerContactTypeEnum::CC_TYPE_WHATSAPP,
        bool   $isCheck = false
    )
    {
        $url = ProductConfig::getInstance()->getBaseUrl() . '/v1/order/payment-points';
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

        return [];
    }

    public function getTransactions(string $transactionId = '')
    {
        $url = ProductConfig::getInstance()->getBaseUrl() . '/v1/order/payment-points';

        if (!blank($transactionId)) {
            $url = $url . '/' . $transactionId;
        }

        $response = HttpClient::request()->post($url);

        if ($response->ok()) {
            return collect($response->json()['data'])->toArray();
        }

        return [];
    }

    public function checkTransaction(string $transactionId)
    {
        return $this->getTransactions($transactionId);
    }
}