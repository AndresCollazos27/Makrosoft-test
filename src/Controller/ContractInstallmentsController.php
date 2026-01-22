<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ContractRepository;
use App\Service\ContractInstallmentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/contracts')]
final class ContractInstallmentsController extends AbstractController
{
    #[Route('/{id<\d+>}/installments/projection', name: 'api_contracts_installments_projection', methods: ['GET'])]
    public function projection(int $id, Request $request, ContractRepository $contracts, ContractInstallmentService $service): JsonResponse
    {
        $contract = $contracts->find($id);
        if ($contract === null) {
            return $this->json(['error' => 'Contract not found'], 404);
        }

        $months = $this->parseMonths($request);
        if ($months === null) {
            return $this->json(['error' => 'months must be a positive integer'], 400);
        }

        $installments = $service->projectOnly($contract, $months);

        return $this->json([
            'contractId' => $contract->getId(),
            'months' => $months,
            'method' => $contract->getMetodoPagoSeleccionado(),
            'installments' => $installments,
        ]);
    }

    #[Route('/{id<\d+>}/installments', name: 'api_contracts_installments_generate', methods: ['POST'])]
    public function generateAndPersist(int $id, Request $request, ContractRepository $contracts, ContractInstallmentService $service): JsonResponse
    {
        $contract = $contracts->find($id);
        if ($contract === null) {
            return $this->json(['error' => 'Contract not found'], 404);
        }

        $months = $this->parseMonths($request);
        if ($months === null) {
            return $this->json(['error' => 'months must be a positive integer'], 400);
        }

        $installments = $service->generateAndPersist($contract, $months);

        return $this->json([
            'contractId' => $contract->getId(),
            'months' => $months,
            'method' => $contract->getMetodoPagoSeleccionado(),
            'installments' => $installments,
        ]);
    }

    private function parseMonths(Request $request): ?int
    {
        $raw = $request->query->get('months');
        if (!is_string($raw) || $raw === '' || !ctype_digit($raw)) {
            return null;
        }

        $months = (int) $raw;

        return $months > 0 ? $months : null;
    }
}
