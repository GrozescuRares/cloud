<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 09:34
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;
use AppBundle\Form\AddUserTypeForm;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserManagementController
 * @package AppBundle\Controller
 */
class UserManagementController extends Controller
{

    /**
     * @Route("/user-management/add-user", name="add-user")
     *
     * @param Request $request
     *
     * @throws OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addUserAction(Request $request)
    {
        $user = new User();
        $loggedUser = $this->getUser();
        $userService = $this->get('app.user.service');
        $hotelService = $this->get('app.hotel.service');
        $hotels = $hotelService->getHotelsByOwner($loggedUser);
        $roles = $userService->getUserCreationalRoles($loggedUser);

        $form = $this->createForm(AddUserTypeForm::class, $user, [
            'loggedUser' => $loggedUser,
            'roles'      => $roles,
            'hotels'     => $hotels,
            'validation_groups' => ['register'],
        ]);

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->render(
                'user_management/add-user.html.twig',
                [
                    'add_user_form' => $form->createView(),
                ]
            );
        }

        $userService->addUser($user, $loggedUser);
        $this->addFlash('success', 'Add user form successfully submitted. Thank you !');

        return $this->redirectToRoute('add-user');
    }

    /**
     * @Route("/user-management/", name="user-management")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userManagementAction(Request $request)
    {
        $userService = $this->get('app.user.service');
        $paginator  = $this->get('knp_paginator');

        try {
            $query = $userService->getUsersFromOwnersHotelsQuery($this->getUser());
            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1)/*page number*/,
                5/*limit per page*/
            );
        } catch (NoRoleException $ex) {
            $this->addFlash('danger', $ex->getMessage());
        } catch (InappropriateUserRoleException $ex) {
            $this->addFlash('danger', $ex->getMessage());
        }

        return $this->render('user_management/user-management.html.twig', array('pagination' => $pagination));
    }
}
