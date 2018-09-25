<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Hotel;
use AppBundle\Enum\PaginationConfig;
use AppBundle\Enum\RoomConfig;

/**
 * RoomRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RoomRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param mixed     $hotelId
     * @return mixed
     */
    public function getBookedRooms(\DateTime $startDate, \DateTime $endDate, $hotelId = null)
    {
        $qb = $this->createQueryBuilder('room');
        $qb->innerJoin('room.reservations', 'reservation')
            ->innerJoin('room.hotel', 'hotel')
            ->where('reservation.startDate <= :endDate')
            ->andWhere('reservation.endDate >= :startDate')
            ->groupBy('room.roomId')
            ->setParameter('endDate', $endDate)
            ->setParameter('startDate', $startDate);

        if (!empty($hotelId)) {
            $qb->andWhere('reservation.hotel = :hotelId')
                ->setParameter('hotelId', $hotelId);
        }
        $bookedRooms = $qb->getQuery()->getResult();

        return $bookedRooms;
    }


    /**
     * @param mixed $hotelId
     *
     * @return array
     */
    public function getHotelRooms($hotelId)
    {
        $qb = $this->createQueryBuilder('room');
        $rooms = $qb->innerJoin('room.hotel', 'hotel')
            ->where('room.hotel = :hotelId')
            ->setParameter('hotelId', $hotelId)
            ->getQuery()
            ->getResult();

        return $rooms;
    }

    /**
     * @param Hotel $hotel
     * @param mixed $petFilter
     * @param mixed $smokingFilter
     *
     * @return float
     */
    public function getRoomsPagesNumber(Hotel $hotel, $petFilter = null, $smokingFilter = null)
    {
        $qb = $this->createQueryBuilder('room');
        $qb ->where('room.hotel =:hotel')
            ->setParameter('hotel', $hotel);

        if (!empty($petFilter) || $petFilter === false) {
            $qb->andWhere('room.pet = :petFilter')
                ->setParameter('petFilter', $petFilter);
        }

        if (!empty($smokingFilter) || $smokingFilter === false) {
            $qb->andWhere('room.smoking = :smokingFilter')
                ->setParameter('smokingFilter', $smokingFilter);
        }
        $rooms = $qb->getQuery()->getResult();

        return ceil(count($rooms) / PaginationConfig::ITEMS);
    }

    /**
     * @param Hotel $hotel
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @param mixed $petFilter
     * @param mixed $smokingFilter
     * @return array
     */
    public function paginateAndSortRooms(Hotel $hotel, $offset, $column = null, $sort = null, $petFilter = null, $smokingFilter = null)
    {
        $qb = $this->createQueryBuilder('room');
        $qb ->where('room.hotel =:hotel')
            ->setParameter('hotel', $hotel);

        if ((!empty($petFilter) && $petFilter !== RoomConfig::ALL) || $petFilter === false) {
            $qb->andWhere('room.pet = :petFilter')
                ->setParameter('petFilter', $petFilter);
        }

        if ((!empty($smokingFilter) && $smokingFilter !== RoomConfig::ALL) || $smokingFilter === false) {
            $qb->andWhere('room.smoking = :smokingFilter')
                ->setParameter('smokingFilter', $smokingFilter);
        }
        $qb
            ->setMaxResults(PaginationConfig::ITEMS)
            ->setFirstResult($offset);

        if (!empty($column) and !empty($sort)) {
            $qb->orderBy('room.'.$column, $sort);
        }

        return $qb->getQuery()->getResult();
    }
}
