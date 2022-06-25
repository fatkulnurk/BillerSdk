<?php

namespace Fatkulnurk\BillerSdk\Payments;

use App\Models\PaymentMethod;

abstract class Strategy
{
    public function calculateFee($price, PaymentMethod $paymentMethod)
    {
        $fee = 0;

        if ($paymentMethod->fee_in_idr > 0) {
            $fee += $paymentMethod->fee_in_idr;
        }

        if ($paymentMethod->fee_in_percent > 0) {
            $tempFee = ($paymentMethod->fee_in_percent * $price) / 100;
            $fee = $fee + $tempFee;
        }

        return $fee;
    }

    public function calculateTotal($price, $randomAmount, $fee, $rate, $asFloat = true)
    {
        $total = $price + $randomAmount + $fee;
        $totalRate = $total / $rate;

        if ($asFloat) {
            $totalRateFloat = floatval(number_format($totalRate, 8));
        } else {
            $totalRateFloat = $totalRate;
        }

        return $totalRateFloat;
    }
}