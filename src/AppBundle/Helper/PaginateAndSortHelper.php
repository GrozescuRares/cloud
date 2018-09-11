<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 05.09.2018
 * Time: 09:05
 */

namespace AppBundle\Helper;

use AppBundle\Enum\OrderConfig;
use AppBundle\Enum\PaginationConfig;

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
    public static function configPaginationFilters($column, $sort, $paginate, $pageNumber)
    {
        $offset = PaginationConfig::ITEMS * $pageNumber - PaginationConfig::ITEMS;
        if (!empty($column) && !empty($sort) && !empty($paginate)) {
            $sortType = OrderConfig::TYPE[$sort];
        } else {
            $sortType = $sort;
            if (!empty($column) && !empty($sort)) {
                $sort = OrderConfig::TYPE[$sort];
                $offset = 0;
                $pageNumber = 1;
            }
        }

        return array($sortType, $sort, $offset, $pageNumber);
    }
}
