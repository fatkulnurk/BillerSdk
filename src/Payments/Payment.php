<?php

namespace Fatkulnurk\BillerSdk\Payments;

use App\Enums\PaymentMethodProviderEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Fatkulnurk\BillerSdk\Payments\Duitku\Duitku;
use Fatkulnurk\BillerSdk\Payments\Indodax\Indodax;
use Fatkulnurk\BillerSdk\Payments\Moota\Moota;

class Payment
{
    public PaymentInterface $payment;

    public function getProvider(PaymentMethod $paymentMethod): PaymentInterface
    {
        switch($paymentMethod->provider) {
            case PaymentMethodProviderEnum::PROVIDER_INDODAX:
                return (new Indodax());
            case PaymentMethodProviderEnum::PROVIDER_DUITKU :
                return (new Duitku());
            case PaymentMethodProviderEnum::PROVIDER_MOOTA:
                return (new Moota());
            default:
                throw new \Exception('Payment provider not found');
            }
    }

    public function setPayment(PaymentInterface $paymentProvider): void
    {
        $this->payment = $paymentProvider;
    }

    public function createPayment(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        return $this->payment->create($transaction, $paymentMethod);
    }

    /*
     * if payment success
     * order to provider
     * */
    public function checkStatusPayment(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        return $this->payment->checkStatusPayment($transaction, $paymentMethod);
    }
}