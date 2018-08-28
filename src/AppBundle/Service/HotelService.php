<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 12:15
 */

namespace AppBundle\Service;

use AppBundle\Entity\Hotel;
use AppBundle\Entity\User;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;
use Doctrine\ORM\EntityManager;

/**
 * Class HotelService
 * @package AppBundle\Service
 */
class HotelService
{
    /** @var EntityManager */
    protected $em;

    /**
     * HotelService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
}