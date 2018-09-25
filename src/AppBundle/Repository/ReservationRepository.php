<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Hotel;
use AppBundle\Entity\User;
use AppBundle\Enum\PaginationConfig;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * ReservationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReservationRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Hotel     $hotel
     * @param \DateTime $startYear
     * @param \DateTime $endYear
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return mixed
     */
    public function getAnnualEarnings(Hotel $hotel, \DateTime $startYear, \DateTime $endYear)
    {
        $qb = $this->createQueryBuilder('reservation');
        $qb ->select('SUM(room.price)')
            ->innerJoin('reservation.room', 'room')
            ->where('reservation.hotel = :hotel')
            ->andWhere('reservation.startDate >= :startYear')
            ->andWhere('reservation.startDate <= :endYear ')
            ->setParameter('hotel', $hotel)
            ->setParameter('startYear', $startYear)
            ->setParameter('endYear', $endYear);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Hotel $hotel
     *
     * @return float
     */
    public function getReservationsPagesNumberByHotel(Hotel $hotel)
    {
        $qb = $this->createQueryBuilder('reservation');
        $qb ->where('reservation.hotel = :hotel')
            ->setParameter('hotel', $hotel);

        $reservations = $qb->getQuery()->getResult();

        return ceil(count($reservations) / PaginationConfig::ITEMS);
    }

    /**
     * @param Hotel $hotel
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @return array
     */
    public function paginateAndSortReservationsByHotel(Hotel $hotel, $offset, $column, $sort)
    {
        $qb = $this->createQueryBuilder('reservation');
        $qb ->innerJoin('reservation.hotel', 'hotel')
            ->where('reservation.hotel = :hotel')
            ->setParameter('hotel', $hotel)
            ->setMaxResults(PaginationConfig::ITEMS)
            ->setFirstResult($offset);

        if (!empty($column) and !empty($sort)) {
            $qb->orderBy('reservation.'.$column, $sort);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $hotels
     * @return float
     */
    public function getReservationsPagesNumberForAllHotels(array $hotels)
    {
        $qb = $this->createQueryBuilder('reservation');
        $qb->where('reservation.hotel IN (:hotels)')
            ->setParameter('hotels', $hotels);

        $reservations = $qb->getQuery()->getResult();

        return ceil(count($reservations) / PaginationConfig::ITEMS);
    }

    /**
     * @param array $hotels
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @return array
     */
    public function paginateAndSortReservationsForAllHotels(array $hotels, $offset, $column, $sort)
    {
        $qb = $this->createQueryBuilder('reservation');
        $qb ->innerJoin('reservation.hotel', 'hotel')
            ->where('reservation.hotel IN (:hotels)')
            ->setParameter('hotels', $hotels)
            ->setMaxResults(PaginationConfig::ITEMS)
            ->setFirstResult($offset);

        if ($column == 'hotel') {
            $qb->orderBy('hotel.name', $sort);

            return $qb->getQuery()->getResult();
        }

        if (!empty($column) and !empty($sort)) {
            $qb->orderBy('reservation.'.$column, $sort);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User $client
     * @return float
     */
    public function getUserReservationPagesNumber(User $client)
    {
        $qb = $this->createQueryBuilder('reservation');
        $qb ->where('reservation.user = :client')
            ->setParameter('client', $client);

        $reservations = $qb->getQuery()->getResult();

        return ceil(count($reservations) / PaginationConfig::ITEMS);
    }

    /**
     * @param User  $client
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @return array
     */
    public function paginateAndSortUsersReservations(User $client, $offset, $column = null, $sort = null)
    {
        $qb = $this->createQueryBuilder('reservation');
        $qb ->innerJoin('reservation.hotel', 'hotel')
            ->where('reservation.user = :client')
            ->setParameter('client', $client)
            ->setMaxResults(PaginationConfig::ITEMS)
            ->setFirstResult($offset);

        if ($column == 'hotel') {
            $qb->orderBy('hotel.name', $sort);

            return $qb->getQuery()->getResult();
        }

        if (!empty($client) && !empty($sort)) {
            $qb->addOrderBy('reservation.'.$column, $sort);
        }

        return $qb->getQuery()->getResult();
    }
}