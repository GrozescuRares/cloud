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
        $hotelId = !empty($request->query->get('hotelId')) ? $request->query->get('hotelId') : null;
        $pageNumber = !empty($request->query->get('pageNumber')) ? $request->query->get('pageNumber') : null;
        $column = !empty($request->query->get('column')) ? $request->query->get('column') : null;
        $sort = !empty($request->query->get('sort')) ? $request->query->get('sort') : null;
        $paginate = !empty($request->query->get('paginate')) ? $request->query->get('paginate') : null;
        $petFilter = !empty($request->query->get('petFilter')) ? RoomConfig::CONVERT[$request->query->get('petFilter')] : null;
        $smokingFilter = !empty($request->query->get('smokingFilter')) ? RoomConfig::CONVERT[$request->query->get('smokingFilter')] : null;

        return array($hotelId, $pageNumber, $column, $sort, $paginate, $petFilter, $smokingFilter);
    }

    protected function handleReservation(Request $request)
    {
        $reservation = $request->request->get('appbundle_reservationDto');
        $reservationDto = new ReservationDto();
        $reservationDto->startDate = !empty($reservation['startDate']) ? ValidateReservationHelper::convertToDateTime($reservation['startDate']) : null;
        $reservationDto->endDate = !empty($reservation['endDate']) ? ValidateReservationHelper::convertToDateTime($reservation['endDate']) : null;
        $reservationDto->hotel = !empty($reservation['hotel']) ? $reservation['hotel'] : null;
        $reservationDto->room = !empty($reservation['room']) ? $reservation['room'] : null;

        return $reservationDto;
    }

    protected function getDatesInStringFormat(Request $request)
    {
        $reservation = $request->request->get('appbundle_reservationDto');
        $startDate = !empty($reservation['startDate']) ? $reservation['startDate'] : null;
        $endDate = !empty($reservation['endDate']) ? $reservation['endDate'] : null;

        return array($startDate, $endDate);
    }
}
