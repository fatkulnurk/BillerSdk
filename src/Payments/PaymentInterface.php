<?php

namespace Fatkulnurk\BillerSdk\Payments;

use App\Models\Transaction;
use App\Models\TransactionPayment;

interface PaymentInterface
{
    public function create(Transaction $transaction);
    public function checkStatus(TransactionPayment $transactionPayment);
}