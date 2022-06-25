<?php

namespace Fatkulnurk\BillerSdk\Payments\Indodax;

use App\Enums\TransactionStatusEnum;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Fatkulnurk\BillerSdk\Payments\PaymentInterface;
use Fatkulnurk\BillerSdk\Payments\Strategy;

class Indodax extends Strategy implements PaymentInterface
{
    public function create(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        $price = $transaction->product_price;
        $randomAmount = 0;

        if ($paymentMethod->is_with_random_amount) {
            $randomFloat = rand(0, 49999) / 1000000;
            $randomAmount = number_format($randomFloat, 6);
        }

        $rate = $paymentMethod->rate;
        $fee = $this->calculateFee($price, $paymentMethod);

        $totalRateFloat = $this->calculateTotal(
            $price,
            $randomAmount,
            $fee,
            $rate
        );

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
        $deposits = (new IndodaxClient())->getTransaction(
            $paymentMethod->payment_code,
            now()->subDays(14)->toDateString(),
            now()->toDateString()
        );

        foreach ($deposits as $deposit) {
            if (($deposit['status'] == 'success') && ($deposit['amount'] == $transaction->transactionPayment->total)) {
                $transaction->status = TransactionStatusEnum::STATUS_PROCESS;
                $transaction->save();
            }
        }

        return $transaction;
    }
}