<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 10:51
 */

namespace AppBundle\Controller;

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
     */
    public function addRoomAction(Request $request)
    {
        return $this->render('hotel-management/add-user.html.twig');
    }
}
