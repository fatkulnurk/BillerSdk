<?php

namespace Fatkulnurk\BillerSdk\Payments;

use App\Models\Transaction;
use App\Models\TransactionPayment;
use Fatkulnurk\BillerSdk\Payments\Duitku\Duitku;
use Fatkulnurk\BillerSdk\Payments\Indodax\Indodax;
use Fatkulnurk\BillerSdk\Payments\Moota\Moota;

class Payment
{
    public PaymentInterface $payment;

    public function getProvider(string $provider): PaymentInterface
    {
        return match ($provider) {
            'indodax' => (new Indodax()),
            'duitku' => (new Duitku()),
            'moota' => (new Moota()),
            default => throw new \Exception('Payment provider not found'),
        };
    }

    public function setPayment(PaymentInterface $paymentProvider): void
    {
        $this->payment = $paymentProvider;
    }

    public function createPayment(Transaction $transaction)
    {
        return $this->payment->create($transaction);
    }

    public function checkStatus(TransactionPayment $transactionPayment)
    {
        return $this->payment->checkStatus($transactionPayment);
    }
}