<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 17:48
 */

namespace AppBundle\Enum;

/**
 * Class RoutesConfig
 * @package AppBundle\Enum
 */
class RoutesConfig
{
    const ADD_ROOM = '/hotel-management/add-room';
    const ADD_USER = '/user-management/add-user';
    const DASHBOARD = '/';
    const MY_ACCOUNT = '/my-account';
    const EDIT_MY_ACCOUNT = '/edit-my-account';
    const ACTIVATE_ACCOUNT = '/activate-account';
    const REGISTER = '/register';
    const LOGIN = '/login';
    const EDIT_USER = '/user-management/edit-user';
    const USER_MANAGEMENT = '/user-management/';
    const PAGINATE_AND_SORT = 'user-management/paginate-and-sort';
    const REGISTRATION_CONFIRMATION = '/registration-confirmation';
    const CREATE_BOOKING_LOAD_HOTELS = '/bookings/create-booking/load-hotels';
}
