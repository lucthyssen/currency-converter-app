<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CurrencyRepository;
use App\Repository\ExchangeRateRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Currency;
use App\Entity\ExchangeRate;

final class CurrencyConverterController extends AbstractController
{
    /**
     * Toont de currency converter pagina.
     * 
     * @param CurrencyRepository $currencyRepo
     * @param ExchangeRateRepository $rateRepo
     * @param Request $request
     * 
     * @return Response
     */
    #[Route('/converter', name: 'currency_converter')]
    public function index(
        CurrencyRepository $currencyRepo,
        ExchangeRateRepository $rateRepo,
        Request $request
    ): Response {
        $currencies = $currencyRepo->findAll();
        $convertedRates = [];

        $baseCode = $request->query->get('base_currency');
        $amount = (float) $request->query->get('amount', 1);

        if ($baseCode) {
            $convertedRates = $this->getConvertedRates($currencyRepo, $rateRepo, $baseCode, $amount, $currencies, $convertedRates);
        }

        return $this->render('currency_converter/index.html.twig', [
            'currencies' => $currencies,
            'convertedRates' => $convertedRates,
            'selectedBase' => $baseCode,
            'amount' => $amount
        ]);
    }

    /**
     * Hulpfunctie om de geconverteerde tarieven op te halen.
     * 
     * @param CurrencyRepository $currencyRepo
     * @param ExchangeRateRepository $rateRepo
     * @param string $baseCode
     * @param float $amount
     * @param Currency[] $currencies
     * @param array $convertedRates
     * 
     * @return array<string, float>
     */
    private function getConvertedRates(
        CurrencyRepository $currencyRepo,
        ExchangeRateRepository $rateRepo,
        string $baseCode,
        float $amount,
        array $currencies,
        array $convertedRates
    ): array {
        $baseCurrency = $currencyRepo->findOneBy(['code' => strtoupper($baseCode)]);
        if ($baseCurrency) {
            foreach ($currencies as $currency) {
                if ($currency === $baseCurrency) continue;

                // Laatste rate ophalen
                $rate = $rateRepo->findLatestRate($baseCurrency, $currency);

                if ($rate instanceof ExchangeRate) {
                    $convertedRates[$currency->getCode()] = $amount * $rate->getRate();
                }
            }
        }
        return $convertedRates;
    }
}
