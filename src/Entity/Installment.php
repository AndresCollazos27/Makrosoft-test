<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InstallmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstallmentRepository::class)]
#[ORM\Table(name: 'installment')]
#[ORM\UniqueConstraint(name: 'uniq_contract_installment_number', columns: ['contract_id', 'installment_number'])]
class Installment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'installments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contract $contract = null;

    #[ORM\Column(name: 'installment_number', type: 'integer')]
    private int $installmentNumber;

    #[ORM\Column(name: 'due_date', type: 'datetime_immutable')]
    private \DateTimeImmutable $dueDate;

    #[ORM\Column(name: 'amount', type: 'decimal', precision: 15, scale: 2)]
    private string $amount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function getInstallmentNumber(): int
    {
        return $this->installmentNumber;
    }

    public function setInstallmentNumber(int $installmentNumber): self
    {
        $this->installmentNumber = $installmentNumber;

        return $this;
    }

    public function getDueDate(): \DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeImmutable $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
