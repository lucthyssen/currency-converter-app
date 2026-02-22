<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Form\CurrencyType;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('dashboard/currency')]
final class CurrencyController extends AbstractController
{
    /**
     * Toont een lijst van alle valuta's.
     * 
     * @return Response
     */
    #[Route(name: 'app_currency_index', methods: ['GET'])]
    public function index(CurrencyRepository $currencyRepository): Response
    {
        return $this->render('currency/index.html.twig', [
            'currencies' => $currencyRepository->findAll(),
        ]);
    }

    /**
     * Toont een formulier om een nieuwe valuta toe te voegen, en verwerkt de form submission.
     * 
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * 
     * @return Response
     */
    #[Route('/new', name: 'app_currency_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $currency = new Currency();
        $form = $this->createForm(CurrencyType::class, $currency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($currency);
            $entityManager->flush();

            return $this->redirectToRoute('app_currency_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('currency/new.html.twig', [
            'currency' => $currency,
            'form' => $form,
        ]);
    }

    /**
     * Toont details van een specifieke valuta.
     * 
     * @param Currency $currency
     * 
     * @return Response
     */
    #[Route('/{id}', name: 'app_currency_show', methods: ['GET'])]
    public function show(Currency $currency): Response
    {
        return $this->render('currency/show.html.twig', [
            'currency' => $currency,
        ]);
    }

    /**
     * Toont een formulier om een bestaande valuta te bewerken, en verwerkt de form submission.
     * 
     * @param Request $request
     * @param Currency $currency
     * @param EntityManagerInterface $entityManager
     * 
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_currency_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Currency $currency, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CurrencyType::class, $currency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_currency_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('currency/edit.html.twig', [
            'currency' => $currency,
            'form' => $form,
        ]);
    }

    /**
     * Verwerkt de verwijdering van een valuta.
     * 
     * @param Request $request
     * @param Currency $currency
     * @param EntityManagerInterface $entityManager
     * 
     * @return Response
     */
    #[Route('/{id}', name: 'app_currency_delete', methods: ['POST'])]
    public function delete(Request $request, Currency $currency, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$currency->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($currency);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_currency_index', [], Response::HTTP_SEE_OTHER);
    }
}
