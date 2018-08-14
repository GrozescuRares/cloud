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
use AppBundle\Form\UserRegistrationForm;
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
     * @param Request     $request
     *
     * @param UserService $userService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function registerAction(Request $request, UserService $userService)
    {
        $user = new User();

        $form = $this->createForm(UserRegistrationForm::class, $user);

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->render(
                'registration/register.html.twig',
                [
                    'registration_form' => $form->createView(),
                ]
            );
        }

        $userService->insertUser($user);

        $this->addFlash('success', 'You are now successfully registered.');

        return $this->redirectToRoute('register');
    }

}

