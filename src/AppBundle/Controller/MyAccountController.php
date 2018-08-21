<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 21.08.2018
 * Time: 08:49
 */

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Form\UserTypeForm;

/**
 * Class MyAccountController
 * @package AppBundle\Controller
 */
class MyAccountController extends Controller
{
    /**
     * @Route("/my-account", name="my-account")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function myAccountAction()
    {
        return $this->render('my_account/my-account.html.twig');
    }

    /**
     * @Route("/edit-my-account", name="edit-my-account")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editMyAccountAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserTypeForm::class, $user, [
            'validation_groups' => ['edit-my-account'],
        ]);
        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->render(
                'my_account/edit-my-account.html.twig',
                [
                    'edit_form' => $form->createView(),
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
            ]
        );
    }
}
