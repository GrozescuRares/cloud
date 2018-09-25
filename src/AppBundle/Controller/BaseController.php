<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 07.09.2018
 * Time: 14:24
 */

namespace AppBundle\Controller;

use AppBundle\Dto\ReservationDto;
use AppBundle\Enum\RoomConfig;

use AppBundle\Helper\ValidateReservationHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BaseController
 */
class BaseController extends Controller
{
    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getRequestParameters(Request $request)
    {
        $hotelId = $pageNumber = $column = $sort = $paginate = $petFilter = $smokingFilter = "";
        if (!empty($request->query->get('hotelId'))) {
            $hotelId = $request->query->get('hotelId');
        }
        if (!empty($request->query->get('pageNumber'))) {
            $pageNumber = $request->query->get('pageNumber');
        }
        if (!empty($request->query->get('column'))) {
            $column = $request->query->get('column');
        }
        if (!empty($request->query->get('sort'))) {
            $sort = $request->query->get('sort');
        }
        if (!empty($request->query->get('paginate'))) {
            $paginate = $request->query->get('paginate');
        }
        if (!empty($request->query->get('petFilter'))) {
            $petFilter = RoomConfig::CONVERT[$request->query->get('petFilter')];
        }
        if (!empty($request->query->get('smokingFilter'))) {
            $smokingFilter = RoomConfig::CONVERT[$request->query->get('smokingFilter')];
        }

        return array($hotelId, $pageNumber, $column, $sort, $paginate, $petFilter, $smokingFilter);
    }

    protected function handleReservation(Request $request)
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

    protected function getDatesInStringFormat(Request $request)
    {
        $reservation = $request->request->get('appbundle_reservationDto');
        $startDate = $endDate = "";
        if (!empty($reservation['startDate'])) {
            $startDate = $reservation['startDate'];
        }
        if (!empty($reservation['endDate'])) {
            $endDate = $reservation['endDate'];
        }

        return array($startDate, $endDate);
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function checkIfItsAjaxRequest(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render(
                'error.html.twig',
                [
                    'error' => 'Stay out of here.',
                ]
            );
        }
    }
}
