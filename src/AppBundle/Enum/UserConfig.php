<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 23.08.2018
 * Time: 14:40
 */

namespace AppBundle\Enum;


class UserConfig
{

    const ROLE_MANAGER = 'ROLE_MANAGER';
    const ROLE_OWNER = 'ROLE_OWNER';
    const ROLE_EMPLOYEE = 'ROLE_EMPLOYEE';
    const ROLE_CLIENT = 'ROLE_CLIENT';
    const TOKEN_LIFETIME = '+1 minutes';

    const ROLES = [
        self::ROLE_OWNER,
        self::ROLE_MANAGER,
        self::ROLE_EMPLOYEE,
        self::ROLE_CLIENT,
    ];

    const HIGH_ROLES = [
        self::ROLE_OWNER,
        self::ROLE_MANAGER,
    ];

    const EDITABLE_ROLES = [
        self::ROLE_MANAGER,
        self::ROLE_EMPLOYEE,
    ];
}
