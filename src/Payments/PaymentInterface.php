<?php

namespace Fatkulnurk\BillerSdk\Payments;

use App\Models\PaymentMethod;
use App\Models\Transaction;

interface PaymentInterface
{
    public function create(Transaction $transaction, PaymentMethod $paymentMethod);
    public function checkStatusPayment(Transaction $transactionPayment, PaymentMethod $paymentMethod);
}