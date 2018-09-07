<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 11:10
 */

namespace AppBundle\Controller;

use AppBundle\Dto\ReservationDto;
use AppBundle\Enum\PaginationConfig;
use AppBundle\Exception\HotelNotFoundException;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;
use AppBundle\Exception\RoomNotFoundException;
use AppBundle\Form\ReservationTypeForm;

use AppBundle\Helper\PaginateAndSortHelper;
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
    public function loadData(Request $request)
    {
        $this->checkIfItsAjaxRequest($request);

        $reservationDto = $this->handleReservation($request);
        $bookingsManager = $this->get('app.bookings.manager');
        $availableHotels = $bookingsManager->getFreeHotels($reservationDto->startDate, $reservationDto->endDate);
        $availableRooms = [];
        $showHotel = true;
        $showRooms = false;
        $showSave = false;

        if (!empty($reservationDto->hotel)) {
            $availableRooms = $bookingsManager->getFreeRooms($reservationDto->hotel, $reservationDto->startDate, $reservationDto->endDate);
            $showRooms = true;
            $showSave = true;
        }

        if (empty($availableHotels)) {
            $this->addFlash('danger', 'There are no available hotels in that period.');
            $showHotel = false;
        }
        $form = $this->createForm(
            ReservationTypeForm::class,
            $reservationDto,
            [
                'hotels' => $availableHotels,
                'rooms'  => $availableRooms,
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
    public function handleBookFormSubmission(Request $request)
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
                    'nrReservations' => count($reservationDtos),
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
    public function paginateAndSortReservations(Request $request)
    {
        $this->checkIfItsAjaxRequest($request);

        $loggedUser = $this->getUser();
        $hotelManagementManager = $this->get('app.hotel-management.manager');
        $bookingManager = $this->get('app.bookings.manager');

        try {
            list($hotelId, $pageNumber, $column, $sort, $paginate, $petFilter, $smokingFilter) = $this->getRequestParameters(
                $request
            );

            list($sortType, $sort) = PaginateAndSortHelper::configPaginationFilters($column, $sort, $paginate);
            $hotels = $hotelManagementManager->getOwnedHotels($loggedUser);

            if ($hotelId === 'all') {
                $nrPages = $bookingManager->getReservationsPagesNumberForAllHotels($hotels);
                $reservationDtos = $bookingManager->paginateAndSortReservationsForAllHotels(
                    $hotels,
                    $pageNumber * PaginationConfig::ITEMS - PaginationConfig::ITEMS,
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
                        'nrReservations' => count($reservationDtos),
                        'sortBy' => [
                            $column => $sort,
                        ],
                    ]
                );
            }

            $nrPages = $bookingManager->getReservationsPagesNumberByHotel($hotelId);
            $reservationDtos = $bookingManager->paginateAndSortReservationsByHotel(
                $hotelId,
                $pageNumber * PaginationConfig::ITEMS - PaginationConfig::ITEMS,
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
                    'nrReservations' => count($reservationDtos),
                    'sortBy' => [
                        $column => $sort,
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
     * @Route("/bookings/delete-reservation/{reservationId}", name="delete-reservation")
     *
     * @param mixed $reservationId
     */
    public function deleteReservation($reservationId)
    {
    }
}
