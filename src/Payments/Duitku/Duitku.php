<?php

namespace Fatkulnurk\BillerSdk\Payments\Duitku;

use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Exception;
use Fatkulnurk\BillerSdk\Payments\PaymentInterface;
use Fatkulnurk\BillerSdk\Payments\Strategy;

class Duitku extends Strategy implements PaymentInterface
{
    public function create(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        $price = $transaction->product_price;
        $randomAmount = 0;
        $rate = $paymentMethod->rate;
        $fee = $this->calculateFee($price, $paymentMethod);

        $total = $this->calculateTotal(
            $price,
            $randomAmount,
            $fee,
            $rate,
            false
        );

        $duitkuConfig = DuitkuClient::config();

        $paymentAmount = $total; // Amount
        $paymentMethod = $paymentMethod->payment_code; // Permata Bank Virtual Account
        $email = $transaction->customer_contact; // your customer email
//        $phoneNumber = "081234567890"; // your customer phone number (optional)
        $productDetails = "Invoice " . $transaction->id;
        $merchantOrderId = $transaction->id; // from merchant, unique
        $additionalParam = ''; // optional
        $merchantUserInfo = ''; // optional
        $customerVaName = 'Tagihan ' . $transaction->id; // display name on bank confirmation display
        $callbackUrl = route('transactions.show', $transaction->id); // url for callback
        $returnUrl = route('transactions.show', $transaction->id); // url for redirect
        $expiryPeriod = 60 * 12; // set the expired time in minutes


        // Item Details
        $item1 = array(
            'name' => $productDetails,
            'price' => $paymentAmount,
            'quantity' => 1
        );

        $itemDetails = array(
            $item1
        );

        $params = array(
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => $additionalParam,
            'merchantUserInfo' => $merchantUserInfo,
            'customerVaName' => $customerVaName,
            'email' => $email,
//            'phoneNumber' => $phoneNumber,
            'itemDetails' => $itemDetails,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'expiryPeriod' => $expiryPeriod
        );

        try {
            // createInvoice Request
            $responseDuitkuApi = \Duitku\Api::createInvoice($params, $duitkuConfig);
            $responseDecode = json_decode($responseDuitkuApi);
            $responseArray = collect($responseDecode)->toArray();

            dd($responseArray);
            echo $responseDuitkuApi;
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $transactionPayment = TransactionPayment::create([
            'transaction_id' => $transaction->id,
            'currency' => $paymentMethod->currency,
            'payment_number' => $paymentMethod->account_number,
            'unique_amount' => $randomAmount,
            'fee' => $fee,
            'total' => $totalRateFloat,
            'expired_at' => now()->addHours(12)->timestamp
        ]);

        return $transactionPayment;
    }

    public function checkStatusPayment(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        $duitkuConfig = DuitkuClient::config();

        try {
            $merchantOrderId = "YOUR_MERCHANTORDERID";
            $transactionList = \Duitku\Api::transactionStatus($merchantOrderId, $duitkuConfig);

            header('Content-Type: application/json');
            $transaction = json_decode($transactionList);

            // var_dump($transactionList);

            if ($transaction->statusCode == "00") {
                // Action Success
            } else if ($transaction->statusCode == "01") {
                // Action Pending
            } else {
                // Action Failed Or Expired
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}