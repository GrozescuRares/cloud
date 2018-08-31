<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 11:10
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BookingsController
 * @package AppBundle\Controller
 */
class BookingsController extends Controller
{
    /**
     * @param Request $request
     */
    public function createBookingAction(Request $request)
    {
        $this->render('bookings/create-booking.html.twig');
    }
}
