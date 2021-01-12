<?php

namespace APP\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent; 

class PretSubscriber implements ViewEvent
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }
    public static function getAuthenticatedUser(ViewEvent $event)
    {
        return[
            KernelEvents::VIEW => ['getAuthenticatedUser', EventPriorities::PRE_WRITE]
        ];
    }
 
    public function getAuthenticatedUser(GetResponseForControllerResultEvent $event)
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethode();
        $adherent = $this->Token->getToken()->getUser();
        if ($entity instanceof Pret && $method == Request::METHOD_POST){
            $entity->setAdherent($adherent);
        }
        return;
    }
}