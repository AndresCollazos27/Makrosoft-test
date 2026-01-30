<?php

declare(strict_types=1);

namespace App\Domain\Payment;

final class DaviviendaCalculator extends AbstractPaymentCalculator
{
    public function __construct()
    {
        parent::__construct('0.03', '0.03', 'davivienda');
    }
}
