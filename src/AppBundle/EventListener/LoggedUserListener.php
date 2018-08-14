<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class LoggedUserListener
 * @package AppBundle\EventListener
 */
class LoggedUserListener
{
    private $tokenStorage;
    private $router;

    /**
     * LoggedUserListener constructor.
     * @param TokenStorageInterface $t
     * @param RouterInterface       $r
     */
    public function __construct(TokenStorageInterface $t, RouterInterface $r)
    {
        $this->tokenStorage = $t;
        $this->router = $r;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {

        if ($this->isUserLogged() && $event->isMasterRequest()) {
            $currentRoute = $event->getRequest()->attributes->get('_route');

            if ($this->isAuthenticatedUserOnAnonymousPage($currentRoute)) {
                $response = new RedirectResponse($this->router->generate('dashboard'));
                $event->setResponse($response);
            }
        }
    }

    /**
     * @return bool
     */
    private function isUserLogged()
    {
        if (! $this->tokenStorage->getToken()) {
            return false;
        }
        $user = $this->tokenStorage->getToken()->getUser();

        return $user instanceof User;
    }

    /**
     * @param $currentRoute
     * @return bool
     */
    private function isAuthenticatedUserOnAnonymousPage($currentRoute)
    {
        return in_array(
            $currentRoute,
            ['login', 'register']
        );
    }
}
