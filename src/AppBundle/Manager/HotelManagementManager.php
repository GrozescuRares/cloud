<?php

namespace AppBundle\Manager;

use AppBundle\Entity\User;
use AppBundle\Helper\CollectionModifier;
use AppBundle\Service\HotelService;

/**
 * Class HotelManagementManager
 * @package AppBundle\Manager
 */
class HotelManagementManager
{
    /** @var HotelService */
    protected $hotelService;

    /**
     * HotelManagementManager constructor.
     * @param HotelService $hotelService
     */
    public function __construct(HotelService $hotelService)
    {
        $this->hotelService = $hotelService;
    }

    /**
     * @param User $owner
     * @return array
     */
    public function getOwnerHotelsForChoiceType(User $owner)
    {
        return CollectionModifier::addKeyValueToCollection($this->hotelService->getOwnerHotelsDto($owner), 'Please choose Hotel', null);
    }
}
