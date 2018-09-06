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
use AppBundle\Enum\RoomConfig;
use AppBundle\Exception\HotelNotFoundException;
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
            list($hotelId, $pageNumber, $column, $sort, $paginate) = $this->getPaginationParameters($request);
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
        $loggedUser = $this->getUser();
        $hotelManagementManager = $this->get('app.hotel-management.manager');
        $bookingManager = $this->get('app.bookings.manager');
        $hotels = $hotelManagementManager->getOwnedHotels($loggedUser);
        $hotelManagerName = "";

        try {
            if (empty($hotels)) {
                $hotelId = $loggedUser->getHotel()->getHotelId();
                $hotelManagerName = $loggedUser->getHotel()->getName();
            } else {
                $hotelId = reset($hotels)->hotelId;
            }
            $nrPages = $hotelManagementManager->getRoomsPagesNumber($hotelId);
            $roomDtos = $hotelManagementManager->paginateAndSortRooms($hotelId, 0);
            $availableRooms = $bookingManager->getFreeRooms($hotelId, new \DateTime('now'), new \DateTime('now'));

            return $this->render(
                'hotel-management/room-management.html.twig',
                [
                    'managerHotelName' => $hotelManagerName,
                    'firstHotel' => $hotelId,
                    'hotels' => $hotels,
                    'user' => $loggedUser,
                    'rooms' => $roomDtos,
                    'nrPages' => $nrPages,
                    'currentPage' => 1,
                    'availableRooms' => $availableRooms,
                    'nrRooms' => count($roomDtos),
                    'sortBy' => [],
                    'filters' => [
                        'petFilter' => "",
                        'smokingFilter' => "",
                    ],
                ]
            );
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (HotelNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("/hotel-management/paginate-and-sort-rooms", name="paginate-and-sort-rooms")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paginateAndSortRoomsAction(Request $request)
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
            list($hotelId, $pageNumber, $column, $sort, $paginate, $petFilter, $smokingFilter) = $this->getPaginationParameters($request);
            $nrPages = $hotelManagementManager->getRoomsPagesNumber($hotelId, $petFilter, $smokingFilter);

            list($sortType, $sort) = PaginateAndSortHelper::configPaginationFilters($column, $sort, $paginate);
            $roomDtos = $hotelManagementManager->paginateAndSortRooms($hotelId, $pageNumber * PaginationConfig::ITEMS - PaginationConfig::ITEMS, $column, $sortType, $petFilter, $smokingFilter);
            $availableRooms = $bookingManager->getFreeRooms($hotelId, new \DateTime('now'), new \DateTime('now'));

            return $this->render(
                'hotel-management/rooms-table.html.twig',
                [
                    'user' => $loggedUser,
                    'rooms' => $roomDtos,
                    'nrPages' => $nrPages,
                    'currentPage' => $pageNumber,
                    'availableRooms' => $availableRooms,
                    'nrRooms' => count($roomDtos),
                    'sortBy' => [
                        $column => $sort,
                    ],
                    'filters' => [
                        'petFilter' => RoomConfig::ALLOWED[$petFilter],
                        'smokingFilter' => RoomConfig::ALLOWED[$smokingFilter],
                    ],
                ]
            );

        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (HotelNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("/hotel-management/filter-rooms", name="filter-rooms")
     *
     * @param Request $request
     * @return Response
     */
    public function filterRoomsAction(Request $request)
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
            list($hotelId, $pageNumber, $petFilter, $smokingFilter) = $this->getFilterParameters($request);
            $nrPages = $hotelManagementManager->getRoomsPagesNumber($hotelId, $petFilter, $smokingFilter);

            $roomDtos = $hotelManagementManager->paginateAndSortRooms($hotelId, $pageNumber * PaginationConfig::ITEMS - PaginationConfig::ITEMS, null, null, $petFilter, $smokingFilter);
            $availableRooms = $bookingManager->getFreeRooms($hotelId, new \DateTime('now'), new \DateTime('now'));

            return $this->render(
                'hotel-management/rooms-table.html.twig',
                [
                    'user' => $loggedUser,
                    'rooms' => $roomDtos,
                    'nrPages' => $nrPages,
                    'currentPage' => $pageNumber,
                    'availableRooms' => $availableRooms,
                    'nrRooms' => count($roomDtos),
                    'sortBy' => [],
                    'filters' => [
                        'petFilter' => RoomConfig::ALLOWED[$petFilter],
                        'smokingFilter' => RoomConfig::ALLOWED[$smokingFilter],
                    ],
                ]
            );

        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (HotelNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getPaginationParameters(Request $request)
    {
        $hotelId = $request->query->get('hotelId');
        $pageNumber = $request->query->get('pageNumber');
        $column = $request->query->get('column');
        $sort = $request->query->get('sort');
        $paginate = $request->query->get('paginate');
        $petFilter = $request->query->get('petFilter');
        $smokingFilter = $request->query->get('smokingFilter');
        $petFilter = RoomConfig::CONVERT[$petFilter];
        $smokingFilter = RoomConfig::CONVERT[$smokingFilter];

        return array($hotelId, $pageNumber, $column, $sort, $paginate, $petFilter, $smokingFilter);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getFilterParameters(Request $request)
    {
        $hotelId = $request->query->get('hotelId');
        $pageNumber = $request->query->get('pageNumber');
        $petFilter = $request->query->get('petFilter');
        $smokingFilter = $request->query->get('smokingFilter');
        $petFilter = RoomConfig::CONVERT[$petFilter];
        $smokingFilter = RoomConfig::CONVERT[$smokingFilter];

        return array($hotelId, $pageNumber, $petFilter, $smokingFilter);
    }
}
