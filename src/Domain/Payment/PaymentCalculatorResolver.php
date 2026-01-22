<?php

declare(strict_types=1);

namespace App\Domain\Payment;

final class PaymentCalculatorResolver
{
    /**
     * @var array<string, PaymentMethodCalculatorInterface>
     */
    private array $byCode = [];

    /**
     * @param iterable<PaymentMethodCalculatorInterface> $calculators
     */
    public function __construct(iterable $calculators)
    {
        foreach ($calculators as $calculator) {
            $this->byCode[$calculator->getCode()] = $calculator;
        }
    }

    public function resolve(string $method): PaymentMethodCalculatorInterface
    {
        if (!isset($this->byCode[$method])) {
            throw new \InvalidArgumentException('Tipo de pago no soportado');
        }

        return $this->byCode[$method];
    }
}
