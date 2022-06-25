<?php

namespace Fatkulnurk\BillerSdk\Payments\Moota;

use App\Models\PaymentMethod;
use App\Models\Transaction;
use Fatkulnurk\BillerSdk\Payments\PaymentInterface;

class Moota implements PaymentInterface
{
    public function create(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        // TODO: Implement create() method.
    }

    public function checkStatusPayment(Transaction $transaction, PaymentMethod $paymentMethod)
    {
        // TODO: Implement checkStatus() method.
    }
}