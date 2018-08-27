<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 27.08.2018
 * Time: 17:31
 */

namespace AppBundle;


class OrderConfig
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    const TYPE = [
        self::ASC => self::DESC,
        self::DESC => self::ASC,
    ];
}
