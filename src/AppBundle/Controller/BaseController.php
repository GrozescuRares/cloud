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
        $hotelId = $request->query->get('hotelId');
        $pageNumber = $request->query->get('pageNumber');
        $column = $request->query->get('column');
        $sort = $request->query->get('sort');
        $paginate = $request->query->get('paginate');
        $petFilter = $request->query->get('petFilter');
        $smokingFilter = $request->query->get('smokingFilter');
        $petFilter = RoomConfig::CONVERT[$petFilter];
        $smokingFilter = RoomConfig::CONVERT[$smokingFilter];

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
}
