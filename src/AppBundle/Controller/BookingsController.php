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
                    'showHotels'   => false,
                    'showRooms'    => false,
                    'showSave'     => false,
                ]
            );
        }

        return $this->redirectToRoute('create-booking');
    }
}
