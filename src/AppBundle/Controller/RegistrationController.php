<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 13.08.2018
 * Time: 17:02
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use AppBundle\Entity\User;
use AppBundle\Form\Type\MemberType;
use AppBundle\Form\UserTypeForm;
use AppBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class RegistrationController
 * @package AppBundle\Controller
 */
class RegistrationController extends Controller
{

    /**
     * @Route("/register", name="register")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function registerAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserTypeForm::class, $user, [
            'validation_groups' => ['register'],
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registrationConfirmationAction($email)
    {
        return $this->render('registration/confirmation.html.twig', [
            'email' => $email,
        ]);
    }
}
