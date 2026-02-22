<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Repository\AllowedIpRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;

class IpRestrictionSubscriber implements EventSubscriberInterface
{
    private AllowedIpRepository $allowedIpRepo;

    public function __construct(AllowedIpRepository $allowedIpRepo)
    {
        $this->allowedIpRepo = $allowedIpRepo;
    }

    /**
     * Controleert of het IP-adres van de inkomende request is toegestaan.
     * 
     * @param RequestEvent $event
     * 
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        foreach (
            array_map(fn($a) => $a->getIpOrSubnet(), 
            $this->allowedIpRepo->findAll()
        ) as $allowed) {
            // simpel wildcard/subnet match
            if (fnmatch($allowed, $event->getRequest()->getClientIp())) {
                return;
            }
        }

        // IP niet toegestaan
        $event->setResponse(new Response('IP not allowed', 403));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }
}
