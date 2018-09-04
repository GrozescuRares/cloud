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
     * @param User  $loggedUser
     * @param mixed $offset
     * @return array
     */
    public function getFirstHotels(User $loggedUser, $offset)
    {
        return $this->hotelService->getFirstHotels($loggedUser, $offset);
    }
}
