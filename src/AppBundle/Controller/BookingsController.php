<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 11:10
 */

namespace AppBundle\Controller;

use AppBundle\Dto\ReservationDto;
use AppBundle\Form\ReservationTypeForm;
use AppBundle\Helper\ValidateReservationHelper;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BookingsController
 * @package AppBundle\Controller
 */
class BookingsController extends Controller
{
    /**
     * @Route("/bookings/create-booking", name="create-booking")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createBookingAction(Request $request)
    {
        $reservationDto = new ReservationDto();
        $form = $this->createForm(ReservationTypeForm::class, $reservationDto);

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
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

        return $this->redirectToRoute('create-booking');
    }

    /**
     * @Route("/bookings/create-booking/load-hotels", name="load-available-hotels")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadAvailableHotels(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $startDate = ValidateReservationHelper::convertToDateTime($request->query->get('startDate'));
            $endDate = ValidateReservationHelper::convertToDateTime($request->query->get('endDate'));
            $hotelService = $this->get('app.hotel.service');
            $availableHotels = $hotelService->getAvailableHotels($startDate, $endDate);
            $showHotels = true;

            $reservationDto = new ReservationDto();
            $reservationDto->startDate = $startDate;
            $reservationDto->endDate = $endDate;
            $form = $this->createForm(
                ReservationTypeForm::class,
                $reservationDto,
                [
                    'hotels' => $availableHotels,
                ]
            );

            if (empty($availableHotels)) {
                $this->addFlash('danger', 'There are no hotels available in that period.');
                $showHotels = false;
            }

            return $this->render(
                'bookings/load-hotels.html.twig',
                [
                    'booking_form' => $form->createView(),
                    'showHotels' => $showHotels,
                    'showRooms' => false,
                    'showSave' => false,
                ]
            );
        }

        return $this->render(
            'error.html.twig',
            [
                'error' => 'Stay out of here.',
            ]
        );
    }

    /**
     * @Route("/bookings/create-booking/load-rooms", name="load-available-rooms")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadAvailableRooms(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $startDate = ValidateReservationHelper::convertToDateTime($request->query->get('startDate'));
            $endDate = ValidateReservationHelper::convertToDateTime($request->query->get('endDate'));
            $hotelId = $request->query->get('hotelId');
            $hotelService = $this->get('app.hotel.service');
            $hotelDto = $hotelService->getHotelDtoById($hotelId);
            $availableHotels = $hotelService->getAvailableHotels($startDate, $endDate);
            $showHotels = true;
            $roomService = $this->get('app.room.service');
            $availableRooms = $roomService->getAvailableRoomsDtos($hotelId, $startDate, $endDate);
            $showRooms = true;

            $reservationDto = new ReservationDto();
            $reservationDto->startDate = $startDate;
            $reservationDto->endDate = $endDate;
            $reservationDto->hotel = $hotelDto;
            $form = $this->createForm(
                ReservationTypeForm::class,
                $reservationDto,
                [
                    'hotels' => $availableHotels,
                    'rooms'  => $availableRooms,
                ]
            );

            if (empty($availableHotels)) {
                $this->addFlash('danger', 'There are no hotels available in that period.');
                $showHotels = false;
            }

            if (empty($availableRooms)) {
                $this->addFlash('danger', 'There are no rooms available in that period');
                $showRooms = false;
            }

            return $this->render(
                'bookings/load-rooms.html.twig',
                [
                    'booking_form' => $form->createView(),
                    'showHotels' => $showHotels,
                    'showRooms' => $showRooms,
                    'showSave' => true,
                ]
            );
        }

        return $this->render(
            'error.html.twig',
            [
                'error' => 'Stay out of here.',
            ]
        );
    }
}
