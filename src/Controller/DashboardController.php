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
use App\Command\ImportExchangeRatesCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AllowedIpRepository;
use App\Entity\AllowedIp;
use App\Form\AllowedIpType;

final class DashboardController extends AbstractController
{
    /**
     * Toont het dashboard met algemene informatie.
     * 
     * @param CurrencyRepository $currencyRepo
     * 
     * @return Response
     */
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(CurrencyRepository $currencyRepo): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'currencies' => $currencyRepo->findAll(),
        ]);
    }

    /**
     * Toont een overzicht van exchange rates.
     * 
     * @param Request $request
     * @param CurrencyRepository $currencyRepo
     * @param ExchangeRateRepository $rateRepo
     * 
     * @return Response
     */
    #[Route('/rates', name: 'dashboard_exchange_rates')]
    public function rates(Request $request, CurrencyRepository $currencyRepo, ExchangeRateRepository $rateRepo): Response
    {
        $baseCode = $request->query->get('base_currency');
        $targetCode = $request->query->get('target_currency');

        $currencies = $currencyRepo->findAll();
        $rates = [];

        if ($baseCode) {
            $rates = $rateRepo->findRatesForCurrency($baseCode, $targetCode);
        }

        return $this->render('dashboard/rates.html.twig', [
            'currencies' => $currencies,
            'rates' => $rates,
            'selectedBase' => $baseCode,
            'selectedTarget' => $targetCode
        ]);
    }

    /**
     * Importeert exchange rates via de ImportExchangeRatesCommand.
     * 
     * @param string|null $baseCurrency
     * @param ImportExchangeRatesCommand $importCommand
     * @param OutputInterface $output
     * @param EntityManagerInterface $em
     * 
     * @return Response
     */
    #[Route('/dashboard/import-rates/{baseCurrency?}', name: 'dashboard_import_rates')]
    public function importRates(?string $baseCurrency, ImportExchangeRatesCommand $importCommand): Response
    {
        $output = new BufferedOutput();

        $input = new ArrayInput([
            'baseCurrency' => $baseCurrency
        ]);

        $returnCode = $importCommand->run($input, $output);

        $consoleOutput = $output->fetch();

        // Je kunt dit loggen of tonen als flash message
        $this->addFlash('success', "Exchange rates imported successfully!\n");

        return $this->redirectToRoute('dashboard_exchange_rates', [
            'base_currency' => $baseCurrency
        ]);
    }

    /**
     * Toont een lijst van toegestane IP-adressen voor admin toegang.
     * 
     * @param AllowedIpRepository $repo
     * 
     * @return Response
     */
    #[Route('/dashboard/allowed-ips', name: 'admin_allowed_ips')]
    public function allowedIps(AllowedIpRepository $repo): Response
    {
        $ips = $repo->findAll();

        return $this->render('dashboard/allowed_ips.html.twig', [
            'ips' => $ips
        ]);
    }

    /**
     * Toont een formulier om een nieuw IP-adres toe te voegen.
     * 
     * @param Request $request
     * @param EntityManagerInterface $em
     * 
     * @return Response
     */
    #[Route('/dashboard/allowed-ips/create', name: 'admin_create_ip')]
    public function createIp(Request $request, EntityManagerInterface $em): Response
    {
        $ip = new AllowedIp();
        $form = $this->createForm(AllowedIpType::class, $ip);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ip);
            $em->flush();
            $this->addFlash('success', 'IP added successfully!');
            return $this->redirectToRoute('admin_allowed_ips');
        }

        return $this->render('dashboard/ip_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Add Allowed IP'
        ]);
    }

    /**
     * Toont een formulier om een bestaand IP-adres te bewerken.
     * 
     * @param Request $request
     * @param AllowedIp $ip
     * @param EntityManagerInterface $em
     * 
     * @return Response
     */
    #[Route('/dashboard/allowed-ips/edit/{id}', name: 'admin_edit_ip')]
    public function editIp(Request $request, AllowedIp $ip, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AllowedIpType::class, $ip);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'IP updated successfully!');
            return $this->redirectToRoute('admin_allowed_ips');
        }

        return $this->render('dashboard/ip_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edit Allowed IP'
        ]);
    }

    /**
     * Verwijdert een IP-adres uit de toegestane lijst.
     * 
     * @param AllowedIp $ip
     * @param EntityManagerInterface $em
     * 
     * @return Response
     */
    #[Route('/dashboard/allowed-ips/delete/{id}', name: 'admin_delete_ip')]
    public function deleteIp(AllowedIp $ip, EntityManagerInterface $em): Response
    {
        $em->remove($ip);
        $em->flush();
        $this->addFlash('success', 'IP deleted successfully!');

        return $this->redirectToRoute('admin_allowed_ips');
    }
}
