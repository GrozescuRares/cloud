<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 21.08.2018
 * Time: 08:49
 */

namespace AppBundle\Controller;

use AppBundle\Form\UserTypeForm;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\OptimisticLockException;

/**
 * Class MyAccountController
 */
class MyAccountController extends Controller
{
    /**
     * @Route("/my-account", name="my-account")
     *
     * @return Response
     */
    public function myAccountAction()
    {
        return $this->render('my_account/my-account.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/edit-my-account", name="edit-my-account")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws OptimisticLockException
     */
    public function editMyAccountAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserTypeForm::class, $user, [
            'validation_groups' => ['edit-my-account'],
            'type' => 'my-account',
        ]);
        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->render(
                'my_account/edit-my-account.html.twig',
                [
                    'edit_form' => $form->createView(),
                    'user' => $user,
                ]
            );
        }

        $userService = $this->get('app.user.service');
        $userService->updateUser($user);

        $this->addFlash('success', 'Your data was saved.');

        return $this->render(
            'my_account/edit-my-account.html.twig',
            [
                'edit_form' => $form->createView(),
                'user' => $user,
            ]
        );
    }
}
