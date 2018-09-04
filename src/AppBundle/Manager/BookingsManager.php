<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 04.09.2018
 * Time: 11:11
 */

namespace AppBundle\Manager;

use AppBundle\Service\HotelService;
use AppBundle\Service\RoomService;

/**
 * Class BookingsManager
 * @package AppBundle\Manager
 */
class BookingsManager
{
    /** @var HotelService */
    protected $hotelService;
    /** @var RoomService */
    protected $roomService;

    /**
     * BookingsManager constructor.
     *
     * @param HotelService $hotelService
     * @param RoomService  $roomService
     */
    public function __construct(HotelService $hotelService, RoomService $roomService)
    {
        $this->hotelService = $hotelService;
        $this->roomService = $roomService;
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return array
     */
    public function getFreeHotels(\DateTime $startDate, \DateTime $endDate)
    {
        return $this->hotelService->getAvailableHotels($startDate, $endDate);
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param mixed     $hotelId
     * @return array
     */
    public function getFreeRooms(\DateTime $startDate, \DateTime $endDate, $hotelId)
    {
        return $this->roomService->getAvailableRooms($hotelId, $startDate, $endDate);
    }
}
