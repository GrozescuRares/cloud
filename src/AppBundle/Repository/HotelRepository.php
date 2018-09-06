<?php

namespace AppBundle\Repository;

use Symfony\Component\Validator\Constraints\Date;

/**
 * HotelRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class HotelRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return array
     */
    public function getHotelsWithReservations()
    {
        $hotels = $this->createQueryBuilder('hotel')
            ->innerJoin('hotel.reservations', 'reservations')
            ->groupBy('hotel.hotelId')
            ->getQuery()
            ->getResult();

        return $hotels;
    }
}
