<?php

namespace Fatkulnurk\BillerSdk\Payments\Duitku;

use App\Models\Transaction;
use App\Models\TransactionPayment;
use Fatkulnurk\BillerSdk\Payments\PaymentInterface;

class Duitku implements PaymentInterface
{
    public function create(Transaction $transaction)
    {

    }

    public function checkStatus(TransactionPayment $transactionPayment)
    {
        // TODO: Implement checkStatus() method.
    }
}