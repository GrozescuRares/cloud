<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 11:10
 */

namespace AppBundle\Controller;

use AppBundle\Dto\ReservationDto;
use AppBundle\Enum\RoutesConfig;
use AppBundle\Exception\HotelNotFoundException;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\InvalidDateException;
use AppBundle\Exception\NoRoleException;
use AppBundle\Exception\ReservationNotFoundException;
use AppBundle\Exception\RoomNotFoundException;
use AppBundle\Form\ReservationTypeForm;

use AppBundle\Helper\PaginateAndSortHelper;
use AppBundle\Manager\BookingsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\OptimisticLockException;
use Twig_Error_Runtime;
use Twig_Error_Syntax;
use Twig_Error_Loader;

/**
 * Class BookingsController
 */
class BookingsController extends BaseController
{
    /**
     * @Route("/bookings/create-booking", name="create-booking")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createBookingAction(Request $request)
    {
        $reservationDto = new ReservationDto();
        $form = $this->createForm(ReservationTypeForm::class, $reservationDto);


        return $this->render(
            'bookings/create-booking.html.twig',
            [
                'booking_form' => $form->createView(),
                'showHotels' => false,
                'showRooms' => false,
                'showSave' => false,
            ]
        );
    }

    /**
     * @Route("/bookings/create-booking/load-data", name="load-data")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function loadDataAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render(
                'error.html.twig',
                [
                    'error' => 'Stay out of here.',
                ]
            );
        }

        $reservationDto = $this->handleReservation($request);
        list($startDate, $endDate) = $this->getDatesInStringFormat($request);
        $bookingsManager = $this->get('app.bookings.manager');
        $availableRooms = [];
        $showHotel = true;
        $showRooms = false;
        $showSave = false;

        try {
            $availableHotels = $bookingsManager->getFreeHotels($reservationDto->startDate, $reservationDto->endDate);
        } catch (InvalidDateException $ex) {
            $this->addFlash('danger', $ex->getMessage());

            return $this->render(
                'bookings/reservation-form.html.twig',
                [
                    'booking_form' => $this->createForm(ReservationTypeForm::class, new ReservationDto())->createView(),
                    'showHotels' => false,
                    'showRooms' => false,
                    'showSave' => false,
                ]
            );
        }

        if (!empty($reservationDto->hotel)) {
            $availableRooms = $bookingsManager->getFreeRooms(
                $reservationDto->hotel,
                $reservationDto->startDate,
                $reservationDto->endDate
            );
            $showRooms = true;
            $showSave = true;
        }

        if (empty($availableHotels)) {
            $this->addFlash('danger', 'There are no available hotels in that period.');
            $showHotel = false;
        }
        $reservationDto->startDate = $startDate;
        $reservationDto->endDate = $endDate;
        $form = $this->createForm(
            ReservationTypeForm::class,
            $reservationDto,
            [
                'hotels' => $availableHotels,
                'rooms' => $availableRooms,
            ]
        );

        return $this->render(
            'bookings/reservation-form.html.twig',
            [
                'booking_form' => $form->createView(),
                'showHotels' => $showHotel,
                'showRooms' => $showRooms,
                'showSave' => $showSave,
            ]
        );
    }

    /**
     * @Route("/bookings/handle-booking", name="handle-booking", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     * @throws OptimisticLockException
     * @throws Twig_Error_Syntax
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     */
    public function handleBookFormSubmissionAction(Request $request)
    {
        $validator = $this->get('validator');
        $reservationDto = $this->handleReservation($request);
        $errors = $validator->validate($reservationDto);

        if (count($errors) > 0) {
            $this->addFlash('danger', 'Invalid data !');

            return $this->redirectToRoute('create-booking');
        }

        $loggedUser = $this->getUser();
        $bookingsManager = $this->get('app.bookings.manager');

        try {
            $bookingsManager->addReservation($loggedUser, $reservationDto);
            $this->addFlash('success', 'Booking successfully registered. Check your e-mail.');
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (HotelNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (RoomNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }

        return $this->redirectToRoute('create-booking');
    }


    /**
     * @Route("/bookings/reservation-management", name="reservation-management")
     *
     *
     * @return Response
     */
    public function reservationManagementAction()
    {
        $loggedUser = $this->getUser();
        $hotelManagementManager = $this->get('app.hotel-management.manager');
        $bookingManager = $this->get('app.bookings.manager');
        $hotels = $hotelManagementManager->getOwnedHotels($loggedUser);

        try {
            if (empty($hotels)) {
                $hotelId = $loggedUser->getHotel()->getHotelId();
                $nrPages = $bookingManager->getReservationsPagesNumberByHotel($hotelId);
                $reservationDtos = $bookingManager->paginateAndSortReservationsByHotel($hotelId, 0);
            } else {
                $hotelId = reset($hotels)->hotelId;
                $nrPages = $bookingManager->getReservationsPagesNumberForAllHotels($hotels);
                $reservationDtos = $bookingManager->paginateAndSortReservationsForAllHotels($hotels, 0);
            }

            return $this->render(
                'bookings/reservation-management.html.twig',
                [
                    'firstHotel' => $hotelId,
                    'hotels' => $hotels,
                    'user' => $loggedUser,
                    'reservations' => $reservationDtos,
                    'nrPages' => $nrPages,
                    'currentPage' => 1,
                    'nrItems' => count($reservationDtos),
                    'target' => RoutesConfig::PAGINATE_AND_SORT_RESERVATIONS,
                    'sortBy' => [],
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
     * @Route("/bookings/paginate-and-sort-reservations", name="paginate-and-sort-reservations")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function paginateAndSortReservationsAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render(
                'error.html.twig',
                [
                    'error' => 'Stay out of here.',
                ]
            );
        }

        $loggedUser = $this->getUser();
        $hotelManagementManager = $this->get('app.hotel-management.manager');
        $bookingManager = $this->get('app.bookings.manager');

        try {
            $hotels = $hotelManagementManager->getOwnedHotels($loggedUser);

            return $this->paginateFilterAndSortReservations($loggedUser, $hotels, $bookingManager, $request);
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (HotelNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("/bookings/delete-reservation/{reservationId}", name="delete-reservation")
     *
     * @param mixed   $reservationId
     * @param Request $request
     *
     * @throws OptimisticLockException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     *
     * @return Response
     */
    public function deleteReservationAction($reservationId, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render(
                'error.html.twig',
                [
                    'error' => 'Stay out of here.',
                ]
            );
        }
        $loggedUser = $this->getUser();
        $hotelManagementManager = $this->get('app.hotel-management.manager');
        $bookingManager = $this->get('app.bookings.manager');
        try {
            $hotels = $hotelManagementManager->getOwnedHotels($loggedUser);
            if (empty($hotels)) {
                $bookingManager->deleteReservationByManager($loggedUser->getHotel()->getHotelId(), $reservationId);
            } else {
                $bookingManager->deleteReservationByOwner($hotels, $reservationId);
            }
            $this->addFlash('success', 'Reservation successfully deleted.');

            return $this->paginateFilterAndSortReservations($loggedUser, $hotels, $bookingManager, $request);
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (ReservationNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("/bookings/my-bookings", name="my-bookings")
     *
     * @return Response
     */
    public function myBookingsAction()
    {
        $loggedUser = $this->getUser();
        $bookingsManager = $this->get('app.bookings.manager');

        try {
            $reservationDtos = $bookingsManager->paginateAndSortUserReservations($loggedUser, 0);
            $nrPages = $bookingsManager->getUserReservationsPagesNumber($loggedUser);

            return $this->render(
                'bookings/my-bookings.html.twig',
                [
                    'user' => $loggedUser,
                    'reservations' => $reservationDtos,
                    'currentPage' => 1,
                    'nrPages' => $nrPages,
                    'nrItems' => count($reservationDtos),
                    'target' => RoutesConfig::PAGINATE_AND_SORT_BOOKINGS,
                    'sortBy' => [],
                ]
            );
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (ReservationNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("/bookings/paginate-and-sort-bookings", name="paginate-and-sort-bookings")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function paginateAndSortBookingsAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render(
                'error.html.twig',
                [
                    'error' => 'Stay out of here.',
                ]
            );
        }

        $loggedUser = $this->getUser();
        $bookingsManager = $this->get('app.bookings.manager');

        try {
            return $this->paginateAndSortBookings($loggedUser, $bookingsManager, $request);
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (ReservationNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * @Route("/bookings/delete-booking/{reservationId}", name="delete-booking")
     *
     * @param mixed   $reservationId
     * @param Request $request
     *
     * @throws OptimisticLockException
     * @throws OptimisticLockException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @return Response
     */
    public function deleteBookingAction($reservationId, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render(
                'error.html.twig',
                [
                    'error' => 'Stay out of here.',
                ]
            );
        }
        $loggedUser = $this->getUser();
        $bookingManager = $this->get('app.bookings.manager');

        try {
            $bookingManager->deleteBooking($loggedUser, $reservationId);
            $this->addFlash('success', 'Booking successfully deleted.');

            return $this->paginateAndSortBookings($loggedUser, $bookingManager, $request);
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (ReservationNotFoundException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }

    private function paginateAndSortBookings($loggedUser, BookingsManager $bookingsManager, Request $request)
    {
        list($hotelId, $pageNumber, $column, $sort, $paginate, $items, $petFilter, $smokingFilter) = $this->getRequestParameters(
            $request
        );

        if ($items == 1 && $pageNumber > 1) {
            $pageNumber--;
        }
        list($sortType, $sort, $offset, $pageNumber) = PaginateAndSortHelper::configPaginationFilters($column, $sort, $paginate, $pageNumber);

        $reservationDtos = $bookingsManager->paginateAndSortUserReservations(
            $loggedUser,
            $offset,
            $column,
            $sortType
        );
        $nrPages = $bookingsManager->getUserReservationsPagesNumber($loggedUser);

        return $this->render(
            'bookings/bookings-table.html.twig',
            [
                'user' => $loggedUser,
                'reservations' => $reservationDtos,
                'currentPage' => $pageNumber,
                'nrPages' => $nrPages,
                'nrItems' => count($reservationDtos),
                'target' => RoutesConfig::PAGINATE_AND_SORT_BOOKINGS,
                'sortBy' => [
                    $column => $sort,
                ],
            ]
        );
    }

    private function paginateFilterAndSortReservations(
        $loggedUser,
        $hotels,
        BookingsManager $bookingsManager,
        Request $request
    ) {
        list($hotelId, $pageNumber, $column, $sort, $paginate, $items, $petFilter, $smokingFilter) = $this->getRequestParameters(
            $request
        );
        if ($items == 1 && $pageNumber > 1) {
            $pageNumber--;
        }
        list($sortType, $sort, $offset, $pageNumber) = PaginateAndSortHelper::configPaginationFilters($column, $sort, $paginate, $pageNumber);

        if ($hotelId === 'all') {
            $nrPages = $bookingsManager->getReservationsPagesNumberForAllHotels($hotels);
            $reservationDtos = $bookingsManager->paginateAndSortReservationsForAllHotels(
                $hotels,
                $offset,
                $column,
                $sortType
            );

            return $this->render(
                'bookings/reservations-table.html.twig',
                [
                    'firstHotel' => $hotelId,
                    'hotels' => $hotels,
                    'user' => $loggedUser,
                    'reservations' => $reservationDtos,
                    'nrPages' => $nrPages,
                    'currentPage' => $pageNumber,
                    'nrItems' => count($reservationDtos),
                    'target' => RoutesConfig::PAGINATE_AND_SORT_RESERVATIONS,
                    'sortBy' => [
                        $column => $sort,
                    ],
                ]
            );
        }

        $nrPages = $bookingsManager->getReservationsPagesNumberByHotel($hotelId);
        $reservationDtos = $bookingsManager->paginateAndSortReservationsByHotel(
            $hotelId,
            $offset,
            $column,
            $sortType
        );

        return $this->render(
            'bookings/reservations-table.html.twig',
            [
                'firstHotel' => $hotelId,
                'hotels' => $hotels,
                'user' => $loggedUser,
                'reservations' => $reservationDtos,
                'nrPages' => $nrPages,
                'currentPage' => $pageNumber,
                'nrItems' => count($reservationDtos),
                'target' => RoutesConfig::PAGINATE_AND_SORT_RESERVATIONS,
                'sortBy' => [
                    $column => $sort,
                ],
            ]
        );
    }
}
