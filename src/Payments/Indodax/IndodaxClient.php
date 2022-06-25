<?php

namespace Fatkulnurk\BillerSdk\Payments\Indodax;

use Illuminate\Support\Facades\Http;

class IndodaxClient
{
    // example: cryptoSymbol = 'usdt' | start & end format: yyyy-mm-dd
    public function getTransaction($cryptoSymbol, $start = '2021-07-17', $end = '2022-06-17')
    {
        $apikey = config('setting.payments.indodax.api_key');
        $secretKey = config('setting.payments.indodax.secret_key');
        $url = 'https://indodax.com/tapi';
        $data = [
            'method' => 'transHistory',
            'timestamp' => '1578304294000',
            'recvWindow' => '1578303937000',
            'start' => $start,
            'end' => $end
        ];

        $payload = http_build_query($data, '', '&');
        $sign = hash_hmac('sha512', $payload, $secretKey);
        $headers = [
            'Key' => $apikey,
            'Sign' => $sign
        ];
        $response = Http::asForm()->withHeaders($headers)->post($url, (array) $data);

        if ($response->ok()) {
            return cache()->remember($payload, 30, function () use ($response, $cryptoSymbol) {
                $data = collect($response->json())->toArray();
                if (optional($data)['success']) {
                    $deposits = $data['return']['deposit'];
                    return $deposits[$cryptoSymbol];
                }

                return [];
            });
        }

        return [];
    }
}