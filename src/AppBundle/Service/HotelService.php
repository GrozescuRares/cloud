<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 12:15
 */

namespace AppBundle\Service;

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
}
