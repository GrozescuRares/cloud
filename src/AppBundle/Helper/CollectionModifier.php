<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 12:00
 */

namespace AppBundle\Helper;

/**
 * Class CollectionModifier
 * @package AppBundle\Helper
 */
class CollectionModifier
{
    /**
     * @param array $arr
     * @param mixed $key
     * @param mixed $value
     * @return array
     */
    public static function addKeyValueToCollection(array $arr, $key, $value)
    {
        return $arr[$key] = $value;
    }
}
