<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Currency;
use App\Entity\ExchangeRate;
use App\Repository\CurrencyRepository;
use DateTime;

#[AsCommand(
    name: 'app:import-exchange-rates',
    description: 'Import exchange rates from floatrates.com.',
)]
class ImportExchangeRatesCommand extends Command
{
    protected static $url = 'http://www.floatrates.com/daily/%s.json';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * Configureert de command line argumenten voor deze command.
     * 
     * @return void
     */
    public function configure(): void
    {
        $this
            ->addArgument(
                'baseCurrency',
                InputArgument::OPTIONAL,
                '3-letter currency code to import. If left empty, all currencies will be imported.'
            );
    }

    /**
     * Implementeert de logica om exchange rates te importeren
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currencyRepo = $this->em->getRepository(Currency::class);

        $currenciesToImport = $this->getCurrenciesToImport($currencyRepo, $input->getArgument('baseCurrency'), $output);

        // Dit zal enkel de initial import mogelijk maken
        if (empty($currenciesToImport) && $currencyRepo->count([]) === 0) {
            $currenciesToImport = $this->createDummyBaseCurrency($currencyRepo, $output);
        }

        if (empty($currenciesToImport)) {
            $output->writeln('<error>No currencies to import.</error>');
            return Command::SUCCESS;
        }

        foreach ($currenciesToImport as $baseCurrency) {
            $output->writeln('Importing exchange rates for base currency: ' . $baseCurrency->getCode());

            $rates = $this->getExchangeRates($baseCurrency);

            foreach ($rates as $targetCode => $rateData) {
                $targetCurrency = $currencyRepo->findOneBy(['code' => strtoupper($targetCode)]);
                if (!$targetCurrency instanceof Currency) {
                    $targetCurrency = $this->createNewCurrency($targetCode, $rateData, $output);
                }

                // Altijd een nieuwe ExchangeRate aanmaken (bewaart historie)
                $this->createNewExchangeRate($baseCurrency, $targetCurrency, $rateData['rate']);
            }

            $this->em->flush();
            $output->writeln("<info>Rates for {$baseCurrency->getCode()} imported successfully.</info>");
        }

        $output->writeln('<info>All exchange rates imported successfully.</info>');

        return Command::SUCCESS;
    }

    /**
     * Creëert een dummy base currency (EUR) als er nog geen currencies in de database staan.
     * Dit zorgt ervoor dat de import command kan draaien zonder dat er eerst handmatig een currency toegevoegd hoeft te worden.
     * 
     * @param CurrencyRepository $currencyRepo
     * @param OutputInterface $output
     * 
     * @return Currency[]
     */
    private function createDummyBaseCurrency(CurrencyRepository $currencyRepo, OutputInterface $output): array
    {    
        $output->writeln('<comment>No currencies found in database. Creating dummy base currency "EUR".</comment>');
        $currency = new Currency();
        $currency->setCode('EUR');
        $currency->setName('Euro');
        $this->em->persist($currency);
        $this->em->flush();

        return [$currency];
    }

    /**
     * Maakt een nieuwe ExchangeRate aan en persist deze. Er wordt altijd een nieuwe ExchangeRate gemaakt, zodat de historie bewaard blijft.
     * 
     * @param Currency $baseCurrency
     * @param Currency $targetCurrency
     * @param float $rate
     * 
     * @return ExchangeRate
     */
    private function createNewExchangeRate(Currency $baseCurrency, Currency $targetCurrency, float $rate): ExchangeRate
    {
        $exchangeRate = new ExchangeRate();
        $exchangeRate->setBaseCurrency($baseCurrency);
        $exchangeRate->setTargetCurrency($targetCurrency);
        $exchangeRate->setRate($rate);
        $exchangeRate->setDate(new DateTime()); // timestamp van import
        $this->em->persist($exchangeRate);

        return $exchangeRate;
    }

    /**
     * Maakt een nieuwe Currency aan als deze nog niet bestaat in de database.
      * 
      * @param string $code
      * @param array $rateData
      * @param OutputInterface $output
      * 
      * @return Currency
     */
    private function createNewCurrency(string $code, array $rateData, OutputInterface $output): Currency
    {
        $currency = new Currency();
        $currency->setCode(strtoupper($code));
        $currency->setName($rateData['name'] ?? strtoupper($code));
        $this->em->persist($currency);
        $output->writeln(sprintf('<comment>Created new currency: %s (%s)</comment>', strtoupper($code), $currency->getName()));

        return $currency;
    }

    /**
     * Bepaalt welke currencies geïmporteerd moeten worden op basis van de input argumenten.
     * 
     * @param CurrencyRepository $currencyRepo
     * @param string|null $baseCurrencyCode
     * @param OutputInterface $output
     * 
     * @return Currency[]
     */
    private function getCurrenciesToImport($currencyRepo, ?string $baseCurrencyCode, OutputInterface $output): array
    {
        if ($baseCurrencyCode) {
            $currency = $currencyRepo->findOneBy(['code' => strtoupper($baseCurrencyCode)]);
            if (!$currency instanceof Currency) {
                $output->writeln(sprintf('<error>Currency with code "%s" does not exist in the database.</error>', strtoupper($baseCurrencyCode)));
                return [];
            }
            return [$currency];
        }

        return $currencyRepo->findAll();
    }

    /**
     * Haalt exchange rates op van floatrates.com voor een gegeven base currency.
     * 
     * @param Currency $baseCurrency
      * 
      * @return array<string, array>
     */
    private function getExchangeRates(Currency $baseCurrency): array
    {
        $data = file_get_contents(sprintf(self::$url, strtolower($baseCurrency->getCode())));

        if (!$data) {
            throw new \RuntimeException('Failed to fetch data from floatrates.com');
        }

        $rates = json_decode($data, true);

        if (!$rates) {
            throw new \RuntimeException('Failed to decode JSON');
        }

        return $rates;
    }
}