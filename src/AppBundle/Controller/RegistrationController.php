<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 13.08.2018
 * Time: 17:02
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserTypeForm;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\OptimisticLockException;
use Twig_Error_Loader;
use Twig_Error_Syntax;
use Twig_Error_Runtime;

/**
 * Class RegistrationController
 */
class RegistrationController extends Controller
{

    /**
     * @Route("/register", name="register")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws OptimisticLockException
     * @throws Twig_Error_Syntax
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     */
    public function registerAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserTypeForm::class, $user, [
            'validation_groups' => ['register'],
            'type' => 'register',
        ]);

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->render(
                'registration/register.html.twig',
                [
                    'registration_form' => $form->createView(),
                ]
            );
        }

        $userService = $this->get('app.user.service');
        $userService->registerUser($user);

        return $this->redirectToRoute('registration-confirmation', [
            'email' => $user->getEmail(),
        ]);
    }

    /**
     * @Route("/registration-confirmation/{email}", name="registration-confirmation")
     *
     * @param string $email
     *
     * @return Response
     */
    public function registrationConfirmationAction($email)
    {
        return $this->render('registration/confirmation.html.twig', [
            'email' => $email,
        ]);
    }
}
