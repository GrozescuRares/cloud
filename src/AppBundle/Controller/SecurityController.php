<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 13.08.2018
 * Time: 16:37
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SecurityController
 */
class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     *
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return Response
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            $this->addFlash('danger', $error->getMessage());
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        throw new \RuntimeException("the user should never see this.");
    }
}
