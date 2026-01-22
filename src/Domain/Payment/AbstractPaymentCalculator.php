<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use App\Entity\Contract;

abstract class AbstractPaymentCalculator implements PaymentMethodCalculatorInterface
{
    public function __construct(
        private readonly string $interestRate,
        private readonly string $feeRate,
        private readonly string $code,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function calculateInstallments(Contract $contract, int $months): array
    {
        if ($months <= 0) {
            throw new \InvalidArgumentException('El numero de meses debe ser mayor que cero');
        }

        $total = $contract->getValorTotalContrato();
        $base = bcdiv($total, (string) $months, 2);

        $installments = [];

        for ($i = 1; $i <= $months; $i++) {
            $alreadyPaidBase = bcmul($base, (string) ($i - 1), 2);
            $saldoPendiente = bcsub($total, $alreadyPaidBase, 2);
            $interes = bcmul($saldoPendiente, $this->interestRate, 2);
            $subtotal = bcadd($base, $interes, 2);
            $tarifa = bcmul($subtotal, $this->feeRate, 2);
            $valorCuota = bcadd($subtotal, $tarifa, 2);

            $installments[] = [
                'number' => $i,
                'dueDate' => $contract->getFechaContrato()->modify(sprintf('+%d months', $i)),
                'amount' => $valorCuota,
            ];
        }

        return $installments;
    }
}
