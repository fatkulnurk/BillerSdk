<?php

namespace Fatkulnurk\BillerSdk\Payments\Moota;

use App\Models\Transaction;
use App\Models\TransactionPayment;
use Fatkulnurk\BillerSdk\Payments\PaymentInterface;

class Moota implements PaymentInterface
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