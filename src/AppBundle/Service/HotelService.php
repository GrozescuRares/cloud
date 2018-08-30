<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 12:15
 */

namespace AppBundle\Service;

use AppBundle\Adapter\HotelAdapter;
use AppBundle\Entity\Hotel;
use AppBundle\Entity\User;
use AppBundle\Exception\NoRoleException;
use AppBundle\Helper\ValidateUserHelper;

use Doctrine\ORM\EntityManager;

/**
 * Class HotelService
 * @package AppBundle\Service
 */
class HotelService
{
    /** @var EntityManager */
    protected $em;
    /** @var HotelAdapter */
    protected $hotelAdapter;

    /**
     * HotelService constructor.
     *
     * @param EntityManager $em
     * @param HotelAdapter  $hotelAdapter
     */
    public function __construct(EntityManager $em, HotelAdapter $hotelAdapter)
    {
        $this->em = $em;
        $this->hotelAdapter = $hotelAdapter;
    }

    /**
     * Returns an array of hotels that have the owner_id equal
     * with $owner's id. The elements will look like 'hotel_name' => hotel entity
     *
     * @param User $owner
     *
     * @return array
     */
    public function getHotelsByOwner(User $owner)
    {
        if (empty($owner->getRoles())) {
            throw new NoRoleException('This user has no roles');
        }

        $hotels = $this->em->getRepository(Hotel::class)->findBy(
            [
                'owner' => $owner->getUserId(),
            ]
        );

        $result = [];

        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {
            $result[$hotel->getName()] = $hotel;
        }

        return $result;
    }

    /**
     * @param User $owner
     *
     * @return array
     */
    public function getOwnerHotelsDto(User $owner)
    {
        $userRole = $owner->getRoles();
        ValidateUserHelper::checkIfUserHasRole($userRole);
        ValidateUserHelper::checkIfUserHasRoleOwner($userRole);

        $hotels = $this->em->getRepository(Hotel::class)->findBy(
            [
                'owner' => $owner->getUserId(),
            ]
        );

        $result = $this->hotelAdapter->convertHotelsToHotelDtos($hotels);

        return $result;
    }

    /**
     * @param int $hotelId
     *
     * @return Hotel|null|object
     */
    public function getHotelById($hotelId)
    {
        return $this->em->getRepository(Hotel::class)->findOneBy([
            'hotelId' => $hotelId,
        ]);
    }
}
