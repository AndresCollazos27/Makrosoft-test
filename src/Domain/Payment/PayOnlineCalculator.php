<?php

declare(strict_types=1);

namespace App\Domain\Payment;

final class PayOnlineCalculator extends AbstractPaymentCalculator
{
    public function __construct()
    {
        parent::__construct('0.02', '0.01', 'payonline');
    }
}
