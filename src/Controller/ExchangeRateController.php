<?php

namespace App\Controller;

use App\Entity\ExchangeRate;
use App\Form\ExchangeRateType;
use App\Repository\ExchangeRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('dashboard/exchange/rate')]
final class ExchangeRateController extends AbstractController
{
    /**
     * Toont een lijst van alle exchange rates.
     * 
     * @return Response
     */
    #[Route(name: 'app_exchange_rate_index', methods: ['GET'])]
    public function index(ExchangeRateRepository $exchangeRateRepository): Response
    {
        return $this->render('exchange_rate/index.html.twig', [
            'exchange_rates' => $exchangeRateRepository->findAll(),
        ]);
    }

    /**
     * Toont een formulier om een nieuwe exchange rate toe te voegen, en verwerkt de form submission.
     * 
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * 
     * @return Response
     */
    #[Route('/new', name: 'app_exchange_rate_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $exchangeRate = new ExchangeRate();
        $form = $this->createForm(ExchangeRateType::class, $exchangeRate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exchangeRate);
            $entityManager->flush();

            return $this->redirectToRoute('app_exchange_rate_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exchange_rate/new.html.twig', [
            'exchange_rate' => $exchangeRate,
            'form' => $form,
        ]);
    }

    /**
     * Toont details van een specifieke exchange rate.
     * 
     * @param ExchangeRate $exchangeRate
     * 
     * @return Response
     */
    #[Route('/{id}', name: 'app_exchange_rate_show', methods: ['GET'])]
    public function show(ExchangeRate $exchangeRate): Response
    {
        return $this->render('exchange_rate/show.html.twig', [
            'exchange_rate' => $exchangeRate,
        ]);
    }

    /**
     * Toont een formulier om een bestaande exchange rate te bewerken, en verwerkt de form submission.
     * 
     * @param Request $request
     * @param ExchangeRate $exchangeRate
     * @param EntityManagerInterface $entityManager
     * 
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_exchange_rate_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ExchangeRate $exchangeRate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExchangeRateType::class, $exchangeRate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_exchange_rate_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exchange_rate/edit.html.twig', [
            'exchange_rate' => $exchangeRate,
            'form' => $form,
        ]);
    }

    /**
     * Verwerkt de verwijdering van een exchange rate.
     * 
     * @param Request $request
     * @param ExchangeRate $exchangeRate
     * @param EntityManagerInterface $entityManager
     * 
     * @return Response
     */
    #[Route('/{id}', name: 'app_exchange_rate_delete', methods: ['POST'])]
    public function delete(Request $request, ExchangeRate $exchangeRate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exchangeRate->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($exchangeRate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_exchange_rate_index', [], Response::HTTP_SEE_OTHER);
    }
}
