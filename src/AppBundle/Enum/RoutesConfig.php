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

    const MY_ACCOUNT_ROUTE = "my-account";
    const EDIT_MY_ACCOUNT_ROUTE = "edit-my-account";

    const MY_ACCOUNT_ROUTES = [
        self::MY_ACCOUNT_ROUTE,
        self::EDIT_MY_ACCOUNT_ROUTE
    ];

    const ADD_USER_ROUTE = "add-user";
    const EDIT_USER_ROUTE = "edit-user";
    const USER_MANAGEMENT_ROUTE = "user-management";

    const USER_MANAGEMENT_ROUTES = [
        self::ADD_USER_ROUTE,
        self::EDIT_USER_ROUTE,
        self::USER_MANAGEMENT_ROUTE,
    ];

    const HOTELS_INFORMATION_ROUTE = 'hotels-information';
    const HOTEL_INFORMATION_ROUTE = 'show-hotel-information';
    const EDIT_HOTEL_INFORMATION_ROUTE = 'edit-hotel-information';
    const ROOM_MANAGEMENT_ROUTE = 'room-management';
    const ADD_ROOM_ROUTE = 'add-room';

    const HOTEL_MANAGEMENT_ROUTES = [
      self::HOTELS_INFORMATION_ROUTE,
      self::HOTEL_INFORMATION_ROUTE,
      self::EDIT_HOTEL_INFORMATION_ROUTE,
      self::ROOM_MANAGEMENT_ROUTE,
      self::ADD_ROOM_ROUTE,
    ];

    const RESERVATION_MANAGEMENT_ROUTE = 'reservation-management';
    const MY_BOOKINGS_ROUTE = 'my-bookings';
    const CREATE_BOOKING_ROUTE = 'create-booking';

    const BOOKING_ROUTES = [
      self::RESERVATION_MANAGEMENT_ROUTE,
      self::MY_BOOKINGS_ROUTE,
      self::CREATE_BOOKING_ROUTE,
    ];

    const DASHBOARD_ROUTE = 'dashboard';

    const PAGINATE_AND_SORT_RESERVATIONS = 'paginate-and-sort-reservations';
    const PAGINATE_AND_SORT_HOTELS = 'paginate-and-sort-hotels';
    const PAGINATE_FILTER_AND_SORT_ROOMS = 'paginate-filter-and-sort-rooms';
    const PAGINATE_AND_SORT_USERS = 'paginate-and-sort';
    const PAGINATE_AND_SORT_BOOKINGS = 'paginate-and-sort-bookings';
}
