<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 16.08.2018
 * Time: 12:02
 */

namespace AppBundle\Controller;

use AppBundle\Exception\TokenExpiredException;
use AppBundle\Exception\UserNotFoundException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\OptimisticLockException;

/**
 * Class ActivationController
 */
class ActivationController extends Controller
{
    /**
     * @Route("/activate-account/{activationToken}", name="activate-account")
     *
     * @param string $activationToken
     *
     * @return Response
     *
     * @throws OptimisticLockException
     */
    public function activationAction($activationToken)
    {

        try {
            $userService = $this->get('app.user.service');
            $userService->activateAccount($activationToken);

            return $this->render(
                'activation/activate-account.html.twig',
                [
                    'message' => 'Your account is active now',
                    'success' => true,
                ]
            );
        } catch (UserNotFoundException $exception) {
            return $this->render(
                'error.html.twig',
                [
                    'error' => 'Invalid token',
                ]
            );
        } catch (TokenExpiredException $exception) {
            return $this->render(
                'activation/activate-account.html.twig',
                [
                    'message' => 'That activation link expired, but we already sent a new one to your email',
                    'success' => false,
                ]
            );
        }
    }
}
