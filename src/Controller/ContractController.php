<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contract;
use App\Repository\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/contracts')]
final class ContractController extends AbstractController
{
    #[Route('', name: 'api_contracts_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, ContractRepository $contracts): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON body'], 400);
        }

        $numeroContrato = $payload['numeroContrato'] ?? null;
        $fechaContrato = $payload['fechaContrato'] ?? null;
        $valorTotalContrato = $payload['valorTotalContrato'] ?? null;
        $metodoPagoSeleccionado = $payload['metodoPagoSeleccionado'] ?? null;

        if (!is_string($numeroContrato) || trim($numeroContrato) === '') {
            return $this->json(['error' => 'numeroContrato is required'], 400);
        }

        if ($contracts->findOneBy(['numeroContrato' => $numeroContrato]) !== null) {
            return $this->json(['error' => 'numeroContrato must be unique'], 400);
        }

        if (!is_string($fechaContrato)) {
            return $this->json(['error' => 'fechaContrato is required'], 400);
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $fechaContrato);
        $dateErrors = \DateTimeImmutable::getLastErrors();
        if ($date === false || ($dateErrors !== false && $dateErrors['warning_count'] > 0) || ($dateErrors !== false && $dateErrors['error_count'] > 0)) {
            return $this->json(['error' => 'fechaContrato must be in YYYY-MM-DD format'], 400);
        }

        if (!is_string($valorTotalContrato) || !preg_match('/^\d+(?:\.\d{1,2})?$/', $valorTotalContrato)) {
            return $this->json(['error' => 'valorTotalContrato is required and must be a decimal string'], 400);
        }

        $totalNormalized = bcadd($valorTotalContrato, '0', 2);
        if (bccomp($totalNormalized, '0', 2) <= 0) {
            return $this->json(['error' => 'valorTotalContrato must be > 0'], 400);
        }

        if (!is_string($metodoPagoSeleccionado) || !in_array($metodoPagoSeleccionado, ['paypal', 'payonline'], true)) {
            return $this->json(['error' => 'metodoPagoSeleccionado must be paypal or payonline'], 400);
        }

        $contract = (new Contract())
            ->setNumeroContrato($numeroContrato)
            ->setFechaContrato($date)
            ->setValorTotalContrato($totalNormalized)
            ->setMetodoPagoSeleccionado($metodoPagoSeleccionado);

        $entityManager->persist($contract);
        $entityManager->flush();

        return $this->json([
            'id' => $contract->getId(),
            'numeroContrato' => $contract->getNumeroContrato(),
            'fechaContrato' => $contract->getFechaContrato()->format('Y-m-d'),
            'valorTotalContrato' => $contract->getValorTotalContrato(),
            'metodoPagoSeleccionado' => $contract->getMetodoPagoSeleccionado(),
        ], 201);
    }
}
