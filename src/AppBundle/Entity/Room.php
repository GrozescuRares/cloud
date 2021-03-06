<?php

namespace AppBundle\Entity;

use AppBundle\Enum\RoomConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Room
 *
 * @ORM\Table(name="rooms")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoomRepository")
 */
class Room
{
    /**
     * @var int
     *
     * @ORM\Column(name="roomId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $roomId;

    /**
     * @var int
     *
     * @ORM\Column(name="capacity", type="integer")
     */
    private $capacity;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

    /**
     * @var bool
     *
     * @ORM\Column(name="smoking", type="boolean")
     */
    private $smoking;

    /**
     * @var bool
     *
     * @ORM\Column(name="pet", type="boolean")
     */
    private $pet;

    /**
     * @var Hotel $hotel
     *
     * @ORM\ManyToOne(targetEntity="Hotel", inversedBy="rooms")
     * @ORM\JoinColumn(name="hotel_id", referencedColumnName="hotelId", nullable=true)
     */
    private $hotel;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="Reservation", mappedBy="room")
     */
    private $reservations;

    /**
     * Room constructor.
     */
    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    /**
     * @param array $reservations
     */
    public function setReservations($reservations)
    {
        $this->reservations = $reservations;
    }

    /**
     * @param Reservation $reservation
     * @return $this
     */
    public function addReservation(Reservation $reservation)
    {
        $this->reservations[] = $reservation;

        return $this;
    }

    /**
     * @param Reservation $reservation
     * @return $this
     */
    public function removeReservation(Reservation $reservation)
    {
        $this->reservations->removeElement($reservation);

        return $this;
    }

    /**
     * @return Hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     *
     * @return Room
     */
    public function setHotel($hotel)
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Set capacity
     *
     * @param integer $capacity
     *
     * @return Room
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity
     *
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Room
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set smoking
     *
     * @param boolean $smoking
     *
     * @return Room
     */
    public function setSmoking($smoking)
    {
        $this->smoking = $smoking;

        return $this;
    }

    /**
     * Get smoking
     *
     * @return bool
     */
    public function isSmoking()
    {
        return $this->smoking;
    }

    /**
     * Set pet
     *
     * @param boolean $pet
     *
     * @return Room
     */
    public function setPet($pet)
    {
        $this->pet = $pet;

        return $this;
    }

    /**
     * Get pet
     *
     * @return bool
     */
    public function isPet()
    {
        return $this->pet;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Number: ".$this->getRoomId()." Capacity: ".$this->getCapacity()." Price: ".$this->getPrice()." Smoking: ".RoomConfig::ALLOWED[$this->isSmoking()]." Pet: ".RoomConfig::ALLOWED[$this->isPet()];
    }
}
