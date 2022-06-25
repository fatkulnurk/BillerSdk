<?php

namespace Fatkulnurk\BillerSdk\Payments\Duitku;

use App\Enums\TransactionStatusEnum;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Exception;
use Fatkulnurk\BillerSdk\Payments\PaymentInterface;
use Fatkulnurk\BillerSdk\Payments\Strategy;
use Illuminate\Support\Facades\DB;

class Duitku extends Strategy implements PaymentInterface
{
    public function create(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        $price = $transaction->product_price;

        if ($price < $paymentMethod->min_amount) {
            throw new Exception('Min Amount not reached');
        }

        $randomAmount = 0;
        $rate = $paymentMethod->rate;
        $fee = $this->calculateFee($price, $paymentMethod);

        $total = (int)$this->calculateTotal(
            $price,
            $randomAmount,
            $fee,
            $rate,
            false
        );

        $duitkuConfig = DuitkuClient::config();
        $merchantCode = config('setting.payments.duitku.merchant_code'); // dari duitku
        $apiKey = config('setting.payments.duitku.merchant_key'); //

        $paymentAmount = $total;
        $paymentMethod = $paymentMethod->payment_code; // VC = Credit Card
        $merchantOrderId = $transaction->id; // dari merchant, unik
        $productDetails = 'Pembayaran transaksi: ' . $transaction->id;
        $email = $transaction->customer_contact; // email pelanggan anda
        $phoneNumber = '08123456789'; // nomor telepon pelanggan anda (opsional)
        $additionalParam = ''; // opsional
        $merchantUserInfo = ''; // opsional
        $customerVaName = 'John Doe'; // tampilan nama pada tampilan konfirmasi bank
        $callbackUrl = route('callbacks.duitku', ['txn' => $transaction->id]); // url untuk callback
        $returnUrl = route('transactions.show', $transaction->id); // url untuk redirect
        $expiryPeriod = 60 * config('setting.expired_at'); // atur waktu kadaluarsa dalam hitungan menit
        $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

        // Customer Detail
        $firstName = "John";
        $lastName = "Doe";

        // Address
        $alamat = "Jl. Kembangan Raya";
        $city = "Jakarta";
        $postalCode = "11530";
        $countryCode = "ID";

        $address = array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'address' => $alamat,
            'city' => $city,
            'postalCode' => $postalCode,
            'phone' => $phoneNumber,
            'countryCode' => $countryCode
        );

        $customerDetail = array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'billingAddress' => $address,
            'shippingAddress' => $address
        );

        $itemDetails = array(array(
            'name' => $transaction->product_name,
            'price' => $total,
            'quantity' => 1
            )
        );

        $params = array(
            'merchantCode' => $merchantCode,
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => $additionalParam,
            'merchantUserInfo' => $merchantUserInfo,
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'itemDetails' => $itemDetails,
            'customerDetail' => $customerDetail,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod
        );

        try {
            $params_string = json_encode($params);

            if (config('setting.payments.duitku.is_sandbox')) {
                $url = 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry'; // Sandbox
            } else {
                $url = 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry'; // Production
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($params_string))
            );
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            //execute post
            $request = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode == 200) {
                $result = json_decode($request, true);
                if ($result['statusCode'] == '00') {
                    $transactionPayment = TransactionPayment::create([
                        'transaction_id' => $transaction->id,
                        'currency' => $paymentMethod->currency ?? 'IDR',
                        'payment_number' => optional($result)['vaNumber'] ?? null,
                        'reff_id' => optional($result)['reference'] ?? null,
                        'payment_url' => optional($result)['paymentUrl'] ?? null,
                        'unique_amount' => $randomAmount,
                        'fee' => $fee,
                        'total' => $total,
                        'expired_at' => now()->addHours(config('setting.expired_at'))->timestamp
                    ]);

                    return $transaction;
                } else {
                    throw new Exception($result['statusMessage']);
                }
            } else {
                $request = json_decode($request);
                $errorMessage = "Server Error " . $httpCode . " " . $request->Message;
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $transaction;
    }

    public function checkStatusPayment(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        $duitkuConfig = DuitkuClient::config();

        try {
            $merchantOrderId = $transaction->id;
            $transactionList = \Duitku\Api::transactionStatus($merchantOrderId, $duitkuConfig);
            $transactionDuitku = json_decode($transactionList);

            if ($transactionDuitku->statusCode == "00") {
                DB::beginTransaction();
                try {
                    Transaction::where('id', $transaction->id)->update([
                        'status' => TransactionStatusEnum::STATUS_PROCESS
                    ]);

                    TransactionPayment::where('transaction_id', $transaction->id)->update([
                        'paid_at' => now()->timestamp
                    ]);
                    DB::commit();
                } catch (Exception $exception) {
                    DB::rollBack();
                    dd($exception);
                }
            } else if ($transactionDuitku->statusCode == "01") {
                // Action Pending
            } else {
                // Action Failed Or Expired
            }

            return $transaction;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}