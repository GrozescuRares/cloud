<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 14:38
 */

namespace AppBundle\Helper;

/**
 * Class ValidReservationHelper
 * @package AppBundle\Helper
 */
class ValidateReservationHelper
{
    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param \DateTime $reservedStartDate
     * @param \DateTime $reservedEndDate
     *
     * @return bool
     */
    public static function checkIdDatesAreValid(\DateTime $startDate, \DateTime $endDate, \DateTime $reservedStartDate, \DateTime $reservedEndDate)
    {
        if ($startDate > $endDate) {
            return false;
        }
        if ($startDate === $endDate) {
            return false;
        }
        if ($startDate >= $reservedStartDate && $startDate <= $reservedEndDate) {
            return false;
        }
        if ($endDate >= $reservedStartDate && $endDate <= $reservedEndDate) {
            return false;
        }
        if ($startDate <= $reservedStartDate && $endDate >= $reservedEndDate) {
            return false;
        }

        return true;
    }

    /**
     * @param string $date
     * @return \DateTime
     */
    public static function convertToDateTime($date)
    {
        return new \DateTime($date);
    }
}
