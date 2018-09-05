<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 05.09.2018
 * Time: 09:05
 */

namespace AppBundle\Helper;

use AppBundle\Enum\OrderConfig;

/**
 * Class PaginateAndSortHelper
 * @package AppBundle\Helper
 */
class PaginateAndSortHelper
{
    /**
     * @param mixed $column
     * @param mixed $sort
     * @param mixed $paginate
     *
     * @return array
     */
    public static function configPaginationFilters($column, $sort, $paginate)
    {
        if (!empty($column) && !empty($sort) && !empty($paginate)) {
            $sortType = OrderConfig::TYPE[$sort];
        } else {
            $sortType = $sort;
            if (!empty($column) && !empty($sort)) {
                $sort = OrderConfig::TYPE[$sort];
            }
        }

        return array($sortType, $sort);
    }
}
