<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 09:34
 */

namespace AppBundle\Controller;

use AppBundle\Dto\UserDto;
use AppBundle\Entity\User;
use AppBundle\Exception\SameRoleException;
use AppBundle\Exception\UneditableRoleException;
use AppBundle\Exception\UserNotFoundException;
use AppBundle\Form\EditUserTypeForm;
use AppBundle\Form\UserTypeForm;
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

        $form = $this->createForm(
            UserTypeForm::class,
            $user,
            [
                'type' => 'add-user',
                'loggedUser' => $loggedUser,
                'roles' => $roles,
                'hotels' => $hotels,
                'validation_groups' => ['register'],
            ]
        );

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
     * @Route("/user-management/edit-user", name="edit-user")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws OptimisticLockException
     */
    public function editUserRoleAction(Request $request)
    {
        $userDto = new UserDto();
        $loggedUser = $this->getUser();
        $userService = $this->get('app.user.service');
        $hotelService = $this->get('app.hotel.service');
        $roles = $userService->getUserCreationalRoles($loggedUser);
        $hotels = $hotelService->getHotelsByOwner($loggedUser);

        $form = $this->createForm(
            EditUserTypeForm::class,
            $userDto,
            [
                'validation_groups' => ['edit-user'],
                'roles' => $roles,
            ]
        );

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->render(
                'user_management/edit-user.html.twig',
                [
                    'edit_user_form' => $form->createView(),
                ]
            );
        }

        try {
            $userService->editUserRole($userDto, $loggedUser, $hotels);
            $this->addFlash('success', 'User role successfully edited.');
        } catch (UserNotFoundException $ex) {
            $this->addFlash('danger', $ex->getMessage());
        } catch (SameRoleException $ex) {
            $this->addFlash('danger', $ex->getMessage());
        } catch (UneditableRoleException $ex) {
            $this->addFlash('danger', $ex->getMessage());
        }

        return $this->redirectToRoute('edit-user');
    }
}
