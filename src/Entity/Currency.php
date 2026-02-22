<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 3, unique: true)]
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    // OneToMany van ExchangeRate waar deze currency de baseCurrency is
    #[ORM\OneToMany(mappedBy: 'baseCurrency', targetEntity: ExchangeRate::class)]
    private Collection $exchangeRatesAsBase;

    // OneToMany van ExchangeRate waar deze currency de targetCurrency is
    #[ORM\OneToMany(mappedBy: 'targetCurrency', targetEntity: ExchangeRate::class)]
    private Collection $exchangeRatesAsTarget;

    public function __construct()
    {
        $this->exchangeRatesAsBase = new ArrayCollection();
        $this->exchangeRatesAsTarget = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getExchangeRatesAsBase(): Collection
    {
        return $this->exchangeRatesAsBase;
    }

    public function getExchangeRatesAsTarget(): Collection
    {
        return $this->exchangeRatesAsTarget;
    }

    public function getLatestRate(): ?float
    {
        $latestRate = null;
        foreach ($this->exchangeRatesAsBase as $rate) {
            if ($latestRate === null || $rate->getDate() > $latestRate->getDate()) {
                $latestRate = $rate;
            }
        }

        if ($latestRate === null) {
            foreach ($this->exchangeRatesAsTarget as $rate) {
                if ($latestRate === null || $rate->getDate() > $latestRate->getDate()) {
                    $latestRate = $rate;
                }
            }
        }

        return $latestRate ? $latestRate->getRate() : null;
    }
}
