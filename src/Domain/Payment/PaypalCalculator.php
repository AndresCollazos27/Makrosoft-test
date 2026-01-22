<?php

declare(strict_types=1);

namespace App\Domain\Payment;

final class PaypalCalculator extends AbstractPaymentCalculator
{
    public function __construct()
    {
        parent::__construct('0.01', '0.02', 'paypal');
    }
}
