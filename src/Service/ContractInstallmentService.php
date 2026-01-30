<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Payment\PaymentCalculatorResolver;
use App\Entity\Contract;
use App\Entity\Installment;
use Doctrine\ORM\EntityManagerInterface;

final class ContractInstallmentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentCalculatorResolver $resolver,
    ) {
    }

    /**
     * @return array<int, array{number:int, dueDate:string, amount:string}>
     */
    public function generateAndPersist(Contract $contract, int $months): array
    {
        if ($months <= 0) {
            throw new \InvalidArgumentException('El numero de meses debe ser mayor que cero');
        }

        return $this->entityManager->wrapInTransaction(function () use ($contract, $months): array {
            foreach ($contract->getInstallments()->toArray() as $existing) {
                $contract->removeInstallment($existing);
            }

            $calculator = $this->resolver->resolve($contract->getMetodoPagoSeleccionado());
            $projection = $calculator->calculateInstallments($contract, $months);

            $response = [];

            foreach ($projection as $item) {
                $installment = (new Installment())
                    ->setContract($contract)
                    ->setInstallmentNumber($item['number'])
                    ->setDueDate($item['dueDate'])
                    ->setAmount($item['amount']);

                $contract->addInstallment($installment);
                $this->entityManager->persist($installment);

                $response[] = [
                    'number' => $item['number'],
                    'dueDate' => $item['dueDate']->format('Y-m-d'),
                    'amount' => $item['amount'],
                ];
            }

            $this->entityManager->persist($contract);
            $this->entityManager->flush();

            return $response;
        });
    }

    /**
     * @return array<int, array{number:int, dueDate:string, amount:string}>
     */
    public function projectOnly(Contract $contract, int $months): array
    {
        if ($months <= 0) {
            throw new \InvalidArgumentException('El numero de meses debe ser mayor que cero');
        }

        $calculator = $this->resolver->resolve($contract->getMetodoPagoSeleccionado());
        $projection = $calculator->calculateInstallments($contract, $months);

        $response = [];

        foreach ($projection as $item) {
            $response[] = [
                'number' => $item['number'],
                'dueDate' => $item['dueDate']->format('Y-m-d'),
                'amount' => $item['amount'],
            ];
        }

        return $response;
    }
}
