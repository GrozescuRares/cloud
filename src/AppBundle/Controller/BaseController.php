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
        $parametersName = ['hotelId', 'pageNumber', 'column', 'sort', 'paginate'];
        $requestParameters = [];

        foreach ($parametersName as $parameter) {
            $requestParameters[] = $request->query->get($parameter) ?: null;
        }

        $requestParameters[] = $request->query->get('petFilter') ? RoomConfig::CONVERT[$request->query->get('petFilter')] : null;
        $requestParameters[] = $request->query->get('smokingFilter') ? RoomConfig::CONVERT[$request->query->get('smokingFilter')] : null;

        return $requestParameters;
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
        $parametersName = ['startDate', 'endDate'];
        $requestParameters = [];

        foreach ($parametersName as $parameter) {
            $requestParameters[] = $reservation[$parameter] ?: null;
        }

        return $requestParameters;
    }
}
