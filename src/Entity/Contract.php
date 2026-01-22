<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_contract_numero', columns: ['numero_contrato'])]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'numero_contrato', type: 'string', unique: true)]
    private string $numeroContrato;

    #[ORM\Column(name: 'fecha_contrato', type: 'datetime_immutable')]
    private \DateTimeImmutable $fechaContrato;

    #[ORM\Column(name: 'valor_total_contrato', type: 'decimal', precision: 15, scale: 2)]
    private string $valorTotalContrato;

    #[ORM\Column(name: 'metodo_pago_seleccionado', type: 'string')]
    private string $metodoPagoSeleccionado;

    /**
     * @var Collection<int, Installment>
     */
    #[ORM\OneToMany(mappedBy: 'contract', targetEntity: Installment::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $installments;

    public function __construct()
    {
        $this->installments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroContrato(): string
    {
        return $this->numeroContrato;
    }

    public function setNumeroContrato(string $numeroContrato): self
    {
        $this->numeroContrato = $numeroContrato;

        return $this;
    }

    public function getFechaContrato(): \DateTimeImmutable
    {
        return $this->fechaContrato;
    }

    public function setFechaContrato(\DateTimeImmutable $fechaContrato): self
    {
        $this->fechaContrato = $fechaContrato;

        return $this;
    }

    public function getValorTotalContrato(): string
    {
        return $this->valorTotalContrato;
    }

    public function setValorTotalContrato(string $valorTotalContrato): self
    {
        $this->valorTotalContrato = $valorTotalContrato;

        return $this;
    }

    public function getMetodoPagoSeleccionado(): string
    {
        return $this->metodoPagoSeleccionado;
    }

    public function setMetodoPagoSeleccionado(string $metodoPagoSeleccionado): self
    {
        $this->metodoPagoSeleccionado = $metodoPagoSeleccionado;

        return $this;
    }

    /**
     * @return Collection<int, Installment>
     */
    public function getInstallments(): Collection
    {
        return $this->installments;
    }

    public function addInstallment(Installment $installment): self
    {
        if (!$this->installments->contains($installment)) {
            $this->installments->add($installment);
            $installment->setContract($this);
        }

        return $this;
    }

    public function removeInstallment(Installment $installment): self
    {
        if ($this->installments->removeElement($installment)) {
            if ($installment->getContract() === $this) {
                $installment->setContract(null);
            }
        }

        return $this;
    }
}
