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
     */
    public function testAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserRegistrationForm::class, $user);

        return $this->render(
            'registration/index.html.twig',
            [
                'registration_form' => $form->createView(),
            ]
        );
    }

}

