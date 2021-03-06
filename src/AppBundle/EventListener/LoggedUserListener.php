<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\User;

use AppBundle\Enum\RoutesConfig;
use AppBundle\Enum\UserConfig;
use Symfony\Component\HttpKernel\HttpKernel;
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
     *
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

            if ($this->isAuthenticatedUserOnAnonymousPage($currentRoute) || $this->isOnlyClientRoutes($currentRoute) !== false) {
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
     *
     * @return bool
     */
    private function isAuthenticatedUserOnAnonymousPage($currentRoute)
    {
        return in_array(
            $currentRoute,
            ['login', 'register', 'activate-account']
        );
    }

    /**
     * @param $currentRoute
     * @return bool
     */
    private function isOnlyClientRoutes($currentRoute)
    {
        if (! $this->tokenStorage->getToken()) {
            return false;
        }
        $user = $this->tokenStorage->getToken()->getUser();

        if (!empty($user->getRoles())) {
            $userRole = $user->getRoles()[0];
            if ($userRole !== UserConfig::ROLE_CLIENT) {
                return in_array(
                    $currentRoute,
                    ['create-booking', 'my-bookings']
                );
            }
        }

        return false;
    }
}
