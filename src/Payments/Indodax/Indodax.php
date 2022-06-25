<?php

namespace Fatkulnurk\BillerSdk\Payments\Indodax;

use App\Enums\TransactionStatusEnum;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Exception;
use Fatkulnurk\BillerSdk\Payments\PaymentInterface;
use Fatkulnurk\BillerSdk\Payments\Strategy;

class Indodax extends Strategy implements PaymentInterface
{
    public function create(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        $price = $transaction->product_price;

        if ($price < $paymentMethod->min_amount) {
            throw new Exception('Min Amount not reached');
        }

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
            'expired_at' => now()->addHours(config('setting.expired_at'))->timestamp
        ]);

        return $transactionPayment;
    }

    public function checkStatusPayment(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        $subHours = (int) (config('setting.expired_at') + 19);
        $deposits = (new IndodaxClient())->getTransaction(
            $paymentMethod->payment_code,
            now()->subMonths(10)->subHours($subHours)->toDateString(),
            now()->toDateString()
        );

        foreach ($deposits as $deposit) {
            if (($deposit['status'] == 'success') && ($deposit['amount'] == $transaction->transactionPayment->total)) {
                $reffId = $deposit['deposit_id'];
                $transactionPayment = TransactionPayment::select(['id', 'reff_id'])->where('reff_id', $reffId)->first();
                if (blank($transactionPayment)) {
                    Transaction::where('id', $transaction->id)->update([
                        'status' => TransactionStatusEnum::STATUS_PROCESS
                    ]);

                    TransactionPayment::where('transaction_id', $transaction->id)->update([
                        'paid_at' => now()->timestamp,
                        'reff_id' => $reffId
                    ]);
                }
            }
        }

        return $transaction;
    }
}