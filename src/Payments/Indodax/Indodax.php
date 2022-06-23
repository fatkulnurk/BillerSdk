<?php

namespace Fatkulnurk\BillerSdk\Payments\Indodax;

use App\Models\Transaction;
use App\Models\TransactionPayment;
use Fatkulnurk\BillerSdk\Payments\PaymentInterface;

class Indodax implements PaymentInterface
{
    public function create(Transaction $transaction)
    {
        // TODO: Implement create() method.
    }

    public function checkStatus(TransactionPayment $transactionPayment)
    {
        // TODO: Implement checkStatus() method.
    }
}