<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use App\Entity\Contract;

interface PaymentMethodCalculatorInterface
{
    public function getCode(): string;

    /**
     * @return array<int, array{number:int, dueDate:\DateTimeImmutable, amount:string}>
     */
    public function calculateInstallments(Contract $contract, int $months): array;
}
