<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 11:10
 */

namespace AppBundle\Controller;

use AppBundle\Dto\ReservationDto;
use AppBundle\Entity\Hotel;
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadData(Request $request)
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
        $this->addFlash('success', 'Booking successfully created');

        return $this->redirectToRoute('create-booking');
    }

    private function handleReservation(Request $request)
    {
        $reservation = $request->request->get('appbundle_reservationDto');
        $reservationDto = new ReservationDto();

        if (!empty($reservation['startDate'])) {
            $reservationDto->startDate = ValidateReservationHelper::convertToDateTime($reservation['startDate']);
        }
        if (!empty($reservation['endDate'])) {
            $reservationDto->endDate = ValidateReservationHelper::convertToDateTime($reservation['endDate']);
        }
        if (!empty($reservation['hotel'])) {
            $reservationDto->hotel = $reservation['hotel'];
        }
        if (!empty($reservation['room'])) {
            $reservationDto->room = $reservation['room'];
        }

        return $reservationDto;
    }
}
