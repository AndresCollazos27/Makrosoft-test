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
            return $this->json(['error' => 'numeroContrato es requerido'], 400);
        }

        if ($contracts->findOneBy(['numeroContrato' => $numeroContrato]) !== null) {
            return $this->json(['error' => 'numeroContrato debe ser único'], 400);
        }

        if (!is_string($fechaContrato)) {
            return $this->json(['error' => 'fechaContrato es requerido'], 400);
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $fechaContrato);
        $dateErrors = \DateTimeImmutable::getLastErrors();
        if ($date === false || ($dateErrors !== false && $dateErrors['warning_count'] > 0) || ($dateErrors !== false && $dateErrors['error_count'] > 0)) {
            return $this->json(['error' => 'fechaContrato debe tener el formato YYYY-MM-DD'], 400);
        }

        if (!is_string($valorTotalContrato) || !preg_match('/^\d+(?:\.\d{1,2})?$/', $valorTotalContrato)) {
            return $this->json(['error' => 'valorTotalContrato es requerido y debe ser un string decimal'], 400);
        }

        $totalNormalized = bcadd($valorTotalContrato, '0', 2);
        if (bccomp($totalNormalized, '0', 2) <= 0) {
            return $this->json(['error' => 'valorTotalContrato debe ser mayor que cero'], 400);
        }

        if (!is_string($metodoPagoSeleccionado) || !in_array($metodoPagoSeleccionado, ['paypal', 'payonline','davivienda'], true)) {
            return $this->json(['error' => 'metodoPagoSeleccionado debe ser paypal, payonline o davivienda'], 400);
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
