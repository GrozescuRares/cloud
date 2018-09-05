<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 10:51
 */

namespace AppBundle\Controller;

use AppBundle\Dto\RoomDto;
use AppBundle\Enum\PaginationConfig;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;
use AppBundle\Form\RoomTypeForm;
use AppBundle\Helper\PaginateAndSortHelper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HotelManagementController
 * @package AppBundle\Controller
 */
class HotelManagementController extends Controller
{
    /**
     * @Route("/hotel-management/add-room", name="add-room")
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addRoomAction(Request $request)
    {
        $loggedUser = $this->getUser();
        $hotelManager = $this->get('app.hotel-management.manager');
        $hotels = $hotelManager->getOwnerHotelsForChoiceType($loggedUser);
        $roomDto = new RoomDto();

        $form = $this->createForm(
            RoomTypeForm::class,
            $roomDto,
            [
                'hotels' => $hotels,
            ]
        );

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->render(
                'hotel-management/add-room.html.twig',
                [
                    'add_room_form' => $form->createView(),
                ]
            );
        }

        try {
            $hotelManager->addNewRoom($roomDto);
            $this->addFlash('success', 'The room was successfully added.');

            return $this->redirectToRoute('add-room');
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("hotel-management/hotel-information", name="hotel-information")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function hotelInformationAction(Request $request)
    {
        $loggedUser = $this->getUser();
        $hotelManagementManager = $this->get('app.hotel-management.manager');
        $bookingManager = $this->get('app.bookings.manager');

        try {
            $hotelsDto = $hotelManagementManager->paginateAndSortHotels($loggedUser, 0, null, null);
            $availableHotels = $bookingManager->getFreeHotels(new \DateTime('now'), new \DateTime('now'));
            $pages = $hotelManagementManager->getHotelPagesNumber($loggedUser);

            return $this->render(
                'hotel-management/hotel-information.html.twig',
                [
                    'user' => $loggedUser,
                    'hotels' => $hotelsDto,
                    'availableHotels' => $availableHotels,
                    'nrPages' => $pages,
                    'currentPage' => 1,
                    'nrHotels' => count($hotelsDto),
                    'sortBy' => [],
                ]
            );
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("/hotel-management/paginate-and-sort", name="paginate-and-sort-hotels")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paginateAndSortAction(Request $request)
    {
        $loggedUser = $this->getUser();
        $hotelManagementManager = $this->get('app.hotel-management.manager');
        $bookingManager = $this->get('app.bookings.manager');

        if (!$request->isXmlHttpRequest()) {
            return $this->render(
                'error.html.twig',
                [
                    'error' => 'Stay out of here.',
                ]
            );
        }
        try {
            list($pageNumber, $column, $sort, $paginate) = $this->getPaginationParameters($request);
            $pages = $hotelManagementManager->getHotelPagesNumber($loggedUser);

            list($sortType, $sort) = PaginateAndSortHelper::configPaginationFilters($column, $sort, $paginate);
            $hotelsDto = $hotelManagementManager->paginateAndSortHotels($loggedUser, $pageNumber * PaginationConfig::ITEMS - PaginationConfig::ITEMS, $column, $sortType);
            $availableHotels = $bookingManager->getFreeHotels(new \DateTime('now'), new \DateTime('now'));

            return $this->render(
                'hotel-management/hotels-table.html.twig',
                [
                    'user' => $loggedUser,
                    'hotels' => $hotelsDto,
                    'availableHotels' => $availableHotels,
                    'nrPages' => $pages,
                    'currentPage' => $pageNumber,
                    'nrHotels' => count($hotelsDto),
                    'sortBy' => [
                        $column => $sort,
                    ],
                ]
            );

        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("/hotel-management/room-management", name="room-management")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function roomManagementAction(Request $request)
    {
        return $this->render('hotel-management/room-management.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getPaginationParameters(Request $request)
    {
        $pageNumber = $request->query->get('pageNumber');
        $column = $request->query->get('column');
        $sort = $request->query->get('sort');
        $paginate = $request->query->get('paginate');

        return array($pageNumber, $column, $sort, $paginate);
    }
}
