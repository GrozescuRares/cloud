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
use AppBundle\Form\HotelTypeForm;
use AppBundle\Form\RoomTypeForm;
use AppBundle\Helper\PaginateAndSortHelper;

use AppBundle\Helper\ValidateReservationHelper;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\OptimisticLockException;

/**
 * Class HotelManagementController
 */
class HotelManagementController extends BaseController
{
    /**
     * @Route("/hotel-management/add-room", name="add-room")
     *
     * @param Request $request
     *
     * @return Response
     * @throws OptimisticLockException
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
     * @Route("hotel-management/hotels-information", name="hotels-information")
     *
     * @return Response
     */
    public function hotelInformationAction()
    {
        $loggedUser = $this->getUser();
        $hotelManagementManager = $this->get('app.hotel-management.manager');
        $bookingManager = $this->get('app.bookings.manager');

        try {
            $hotelsDto = $hotelManagementManager->paginateAndSortHotels($loggedUser, 0, null, null);
            $availableHotels = $bookingManager->getFreeHotels(new \DateTime('now'), new \DateTime('now'));
            $pages = $hotelManagementManager->getHotelPagesNumber($loggedUser);

            return $this->render(
                'hotel-management/hotels-information.html.twig',
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
     * @return Response
     */
    public function paginateAndSortHotelsAction(Request $request)
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
            list($hotelId, $pageNumber, $column, $sort, $paginate, $petFilter, $smokingFilter) = $this->getRequestParameters(
                $request
            );
            $pages = $hotelManagementManager->getHotelPagesNumber($loggedUser);

            list($sortType, $sort) = PaginateAndSortHelper::configPaginationFilters($column, $sort, $paginate);
            $hotelsDto = $hotelManagementManager->paginateAndSortHotels(
                $loggedUser,
                $pageNumber * PaginationConfig::ITEMS - PaginationConfig::ITEMS,
                $column,
                $sortType
            );
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
     * @return Response
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
                    'currency' => RoomConfig::CURRENCY,
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
     * @Route("/hotel-management/paginate-filter-and-sort-rooms", name="paginate-filter-and-sort-rooms")
     *
     * @param Request $request
     *
     * @return Response
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
            list($hotelId, $pageNumber, $column, $sort, $paginate, $petFilter, $smokingFilter) = $this->getRequestParameters(
                $request
            );
            $nrPages = $hotelManagementManager->getRoomsPagesNumber($hotelId, $petFilter, $smokingFilter);
            list($sortType, $sort) = PaginateAndSortHelper::configPaginationFilters($column, $sort, $paginate);
            $roomDtos = $hotelManagementManager->paginateAndSortRooms(
                $hotelId,
                $pageNumber * PaginationConfig::ITEMS - PaginationConfig::ITEMS,
                $column,
                $sortType,
                $petFilter,
                $smokingFilter
            );
            $availableRooms = $bookingManager->getFreeRooms($hotelId, new \DateTime('now'), new \DateTime('now'));

            return $this->render(
                'hotel-management/rooms-table.html.twig',
                [
                    'currency' => RoomConfig::CURRENCY,
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
     * @Route("/hotel-management/hotel-information/{hotelId}", name="show-hotel-information")
     *
     * @param mixed $hotelId
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     *
     * @return Response
     */
    public function showHotelInformationAction($hotelId)
    {
        $loggedUser = $this->getUser();
        $hotelService = $this->get('app.hotel.service');
        $reservationService = $this->get('app.reservation.service');
        $bookingManager = $this->get('app.bookings.manager');
        try {
            $hotelDto = $hotelService->getHotelDtoByIdAndOwner($loggedUser, $hotelId);
            $earnings = $reservationService->getAnnualEarnings(
                $hotelId,
                ValidateReservationHelper::convertToDateTime(date('Y').'-1-1'),
                ValidateReservationHelper::convertToDateTime(date('Y').'-12-31')
            );
            $availableHotels = $bookingManager->getFreeHotels(new \DateTime('now'), new \DateTime('now'));
            $availability = !empty($availableHotels[$hotelDto->name]) ? true : false;

            return $this->render(
                'hotel-management/show-hotel-information.html.twig',
                [
                    'hotel' => $hotelDto,
                    'availability' => RoomConfig::AVAILABILITY[$availability],
                    'earnings' => $earnings.RoomConfig::CURRENCY,
                ]
            );
        } catch (HotelNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("/hotel-management/edit-hotel-information/{hotelId}", name="edit-hotel-information")
     *
     * @param mixed   $hotelId
     * @param Request $request
     *
     * @throws OptimisticLockException
     *
     * @return Response
     */
    public function editHotelInformationAction($hotelId, Request $request)
    {
        $loggedUser = $this->getUser();
        $hotelService = $this->get('app.hotel.service');
        try {
            $hotelDto = $hotelService->getHotelDtoByIdAndOwner($loggedUser, $hotelId);

            $form = $this->createForm(HotelTypeForm::class, $hotelDto);

            $form->handleRequest($request);

            if (!($form->isSubmitted() && $form->isValid())) {
                return $this->render(
                    'hotel-management/edit-hotel-information.html.twig',
                    [
                        'edit_form' => $form->createView(),
                        'hotel' => $hotelDto,
                    ]
                );
            }
            $hotelService->updateHotel($hotelDto);
            $this->addFlash('success', 'Hotel information updated successfully !');

            return $this->redirectToRoute('edit-hotel-information', ['hotelId' => $hotelId]);
        } catch (HotelNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }
}
