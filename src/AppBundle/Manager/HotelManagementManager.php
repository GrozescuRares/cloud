<?php

namespace AppBundle\Manager;

use AppBundle\Dto\RoomDto;
use AppBundle\Entity\User;
use AppBundle\Helper\CollectionModifierHelper;
use AppBundle\Service\HotelService;
use AppBundle\Service\RoomService;

/**
 * Class HotelManagementManager
 * @package AppBundle\Manager
 */
class HotelManagementManager
{
    /** @var HotelService */
    protected $hotelService;
    /** @var RoomService */
    protected $roomService;

    /**
     * HotelManagementManager constructor.
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
     * @param User $owner
     * @return array
     */
    public function getOwnerHotelsForChoiceType(User $owner)
    {
        return CollectionModifierHelper::addKeyValueToCollection($this->hotelService->getOwnerHotelsDto($owner), 'Please choose Hotel', null);
    }

    /**
     * @param RoomDto $roomDto
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addNewRoom(RoomDto $roomDto)
    {
        $this->roomService->addRoom($roomDto);
    }

    /**
     * @param User $loggedUser
     * @return float
     */
    public function getHotelPagesNumber(User $loggedUser)
    {
        return $this->hotelService->getHotelsPageNumber($loggedUser);
    }

    /**
     * @param mixed $hotelId
     * @return float
     */
    public function getRoomsPagesNumber($hotelId)
    {
        return $this->roomService->getPagesNumberForRooms($hotelId);
    }

    /**
     * @param User  $loggedUser
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @return array
     */
    public function paginateAndSortHotels(User $loggedUser, $offset, $column, $sort)
    {
        return $this->hotelService->paginateAndSortHotels($loggedUser, $offset, $column, $sort);
    }

    /**
     * @param mixed $hotelId
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @param mixed $petFilter
     * @param mixed $smokingFilter
     * @return array
     */
    public function paginateAndSortRooms($hotelId, $offset, $column = null, $sort = null, $petFilter = null, $smokingFilter = null)
    {
        return $this->roomService->paginateAndSortRooms($hotelId, $offset, $column, $sort, $petFilter, $smokingFilter);
    }

    /**
     * @param User $loggedUser
     * @return array
     */
    public function getOwnedHotels(User $loggedUser)
    {
        return $this->hotelService->getOwnerHotelsDto($loggedUser);
    }
}
