<?php

namespace App\Entity;

use App\Repository\ExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
class ExchangeRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'exchangeRates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $baseCurrency = null;

    #[ORM\ManyToOne(inversedBy: 'exchangeRates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $targetCurrency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 8)]
    private ?string $rate = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseCurrency(): ?Currency
    {
        return $this->baseCurrency;
    }

    public function setBaseCurrency(?Currency $baseCurrency): static
    {
        $this->baseCurrency = $baseCurrency;

        return $this;
    }

    public function getTargetCurrency(): ?Currency
    {
        return $this->targetCurrency;
    }

    public function setTargetCurrency(?Currency $targetCurrency): static
    {
        $this->targetCurrency = $targetCurrency;

        return $this;
    }

    public function getRate(): ?string
    {
        return $this->rate;
    }

    public function setRate(string $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function __construct()
    {
        $this->date = new DateTime();
    }
}
