<?php

namespace App\Repository;

use App\Entity\ExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Currency;

/**
 * @extends ServiceEntityRepository<ExchangeRate>
 */
class ExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    /**
     * Zoekt de meest recente exchange rate tussen twee valuta.
     * 
     * @param Currency $base
     * @param Currency $target
     * 
     * @return ExchangeRate|null
     */
    public function findLatestRate(Currency $base, Currency $target): ?ExchangeRate
    {
        return $this->findOneBy(
            ['baseCurrency' => $base, 'targetCurrency' => $target],
            ['date' => 'DESC']
        );
    }

    /**
     * Zoekt exchange rates voor een gegeven basisvaluta, en optioneel een doelvaluta.
     *
     * @param string $baseCode
     * @param string|null $targetCode
     * 
     * @return ExchangeRate[]
     */
    public function findRatesForCurrency(string $baseCode, ?string $targetCode = null)
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.baseCurrency', 'b')
            ->where('b.code = :baseCode')
            ->setParameter('baseCode', $baseCode)
            ->orderBy('r.date', 'DESC');

        if ($targetCode) {
            $qb->join('r.targetCurrency', 't')
               ->andWhere('t.code = :targetCode')
               ->setParameter('targetCode', $targetCode);
        }

        return $qb->getQuery()->getResult();
    }
}
