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
use AppBundle\OrderConfig;
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
            AddUserTypeForm::class,
            $user,
            [
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
     * @Route("/user-management/", name="user-management")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userManagementAction(Request $request)
    {
        $loggedUser = $this->getUser();
        $userService = $this->get('app.user.service');
        $hotelService = $this->get('app.hotel.service');
        $hotels = $hotelService->getHotelsByOwner($loggedUser);

        try {
            if (empty($hotels)) {
                $users = $userService->getUsersFromHotels($loggedUser, 0);
                $nrPages = $userService->getPagesNumberForManagerManagement($loggedUser);

            } else {
                $query = $userService->getUsersFromHotels($loggedUser, 0, reset($hotels)->getHotelId());
            }

            return $this->render(
                'user_management/user-management.html.twig',
                [
                    'hotels' => $hotels,
                    'user' => $loggedUser,
                    'users' => $users,
                    'nrPages' => $nrPages,
                    'currentPage' => 1,
                    'nrUsers' => count($users),
                    'filters' => [],
                ]
            );
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("/user-management/paginate-and-sort", name="paginate-and-sort")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paginateAndSortAction(Request $request)
    {
        $loggedUser = $this->getUser();
        $userService = $this->get('app.user.service');


        if ($request->isXmlHttpRequest()) {
            $type = $request->query->get('type');

            if ($type === 'managerPagination') {
                $nrPages = $userService->getPagesNumberForManagerManagement($loggedUser);
                $pageNumber = $request->query->get('pageNumber');
                $users = $userService->getUsersFromHotels($loggedUser, $pageNumber * 5 - 5);

                return $this->render(
                    'user_management/table.html.twig',
                    [
                        'users' => $users,
                        'nrPages' => $nrPages,
                        'currentPage' => $pageNumber,
                        'nrUsers' => count($users),
                        'filters' => [
                        ],
                    ]
                );
            }

            if ($type === 'managerSort') {
                $nrPages = $userService->getPagesNumberForManagerManagement($loggedUser);
                $pageNumber = $request->query->get('pageNumber');
                $column = $request->query->get('column');
                $sort = $request->query->get('sort');
                $users = $userService->sortUsersFromManagerHotel($loggedUser, $pageNumber * 5 - 5, $column, $sort);
                $sort = OrderConfig::TYPE[$sort];

                return $this->render(
                    'user_management/table.html.twig',
                    [
                        'users' => $users,
                        'nrPages' => $nrPages,
                        'currentPage' => $pageNumber,
                        'nrUsers' => count($users),
                        'filters' => [
                          $column =>  $sort,
                        ],
                    ]
                );
            }
        }

        return $this->render(
            'error.html.twig',
            [
                'error' => 'Stay out of here.',
            ]
        );
    }
}
