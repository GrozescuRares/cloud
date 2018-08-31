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
            $availableHotels = $hotelService->getAvailableHotelsDto($startDate, $endDate);
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
                'bookings/create-booking-content.html.twig',
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
}
