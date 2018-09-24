<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 13:52
 */

namespace Tests\AppBundle\Controller;

use AppBundle\Enum\RoutesConfig;
use AppBundle\Enum\TestDataConfig;
use Tests\AppBundle\BaseWebTestCase;

/**
 * Class UserManagementControllerTest
 * @package Tests\AppBundle\Controller
 */
class UserManagementControllerTest extends BaseWebTestCase
{
    /**
     * Tests the add-user route with no user logged
     * @group add-user
     */
    public function testAddUserRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::ADD_USER);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests that client user can not access add-user route
     * @group add-user
     */
    public function testThatClientUserCanNotAccessAddUserRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::ADD_USER, TestDataConfig::CLIENT_USER, TestDataConfig::CLIENT_PASSWORD);
    }

    /**
     * Tests that employee user can not access add-user route
     * @group add-user
     */
    public function testThatEmployeeUserCanNotAccessAddUserRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::ADD_USER, 'employee', '12345');
    }

    /**
     * Tests page design when accessed by owner
     * @group add-user
     */
    public function testAddUserPageDesignWhenAccessedByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::ADD_USER, TestDataConfig::OWNER_USER, TestDataConfig::OWNER_PASSWORD);

        $this->assertEquals(5, $crawler->filter('div.second')->children()->count());
    }

    /**
     * Tests page design when accessed by manager
     * @group add-user
     */
    public function testAddUserPageDesignWhenAccessedByManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::ADD_USER, TestDataConfig::MANAGER_USER, TestDataConfig::MANAGER_PASSWORD);

        $this->assertEquals(4, $crawler->filter('div.second')->children()->count());
    }

    /**
     * Tests that owner can successfully add user
     * @group add-user
     */
    public function testSuccessfullyAddUserByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::ADD_USER, TestDataConfig::OWNER_USER, TestDataConfig::OWNER_PASSWORD);

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';

        $form = $crawler->selectButton('appbundle_user[submit]')->form();
        $form = $this->generateForm(
            $form,
            'appbundle_user',
            [
                '[username]' => $username,
                '[email]' => $email,
                '[plainPassword][first]' => '12345',
                '[plainPassword][second]' => '12345',
                '[dateOfBirth][day]' => '1',
                '[dateOfBirth][month]' => '2',
                '[dateOfBirth][year]' => '1950',
                '[firstName]' => "FirstName",
                '[lastName]' => "LastName",
            ]
        );
        $client->submit($form);

        $this->assertRegExp('/\/user-management\/add-user$/', $client->getResponse()->headers->get('location'));

        $crawler = $client->followRedirect();

        $this->assertContains(
            'Add user form successfully submitted. Thank you !',
            $crawler->filter('div.alert')->text()
        );
    }

    /**
     * Tests that manager can successfully add user
     * @group add-user
     */
    public function testSuccessfullyAddUserByManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::ADD_USER, TestDataConfig::MANAGER_USER, TestDataConfig::MANAGER_PASSWORD);

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';

        $form = $crawler->selectButton('appbundle_user[submit]')->form();
        $form = $this->generateForm(
            $form,
            'appbundle_user',
            [
                '[username]' => $username,
                '[email]' => $email,
                '[plainPassword][first]' => '12345',
                '[plainPassword][second]' => '12345',
                '[dateOfBirth][day]' => '1',
                '[dateOfBirth][month]' => '2',
                '[dateOfBirth][year]' => '1950',
                '[firstName]' => "FirstName",
                '[lastName]' => "LastName",
            ]
        );
        $client->submit($form);

        $this->assertRegExp('/\/user-management\/add-user$/', $client->getResponse()->headers->get('location'));

        $crawler = $client->followRedirect();

        $this->assertContains(
            'Add user form successfully submitted. Thank you !',
            $crawler->filter('div.alert')->text()
        );
    }

    /**
     * Tests the edit-user route with no user logged
     * @group edit-user
     */
    public function testEditUserRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::EDIT_USER.'/username');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests that client user can not access edit-user route
     * @group edit-user
     */
    public function testThatClientUserCanNotAccessEditUserRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::EDIT_USER.'/username', TestDataConfig::CLIENT_USER, TestDataConfig::CLIENT_PASSWORD, ['username' => 'username']);
    }

    /**
     * Tests that employee user can not access edit-user route
     * @group edit-user
     */
    public function testThatEmployeeUserCanNotAccessEditUserRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::EDIT_USER.'/username', TestDataConfig::EMPLOYEE_USER, TestDataConfig::EMPLOYEE_PASSWORD, ['username' => 'username']);
    }

    /**
     * Tests page design when accessed by owner
     * @group edit-user
     */
    public function testEditUserPageDesignWhenAccessedByOwnerOrManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/client', TestDataConfig::OWNER_USER, TestDataConfig::OWNER_PASSWORD, ['username' => 'client']);

        $this->assertContains('Edit', $crawler->filter('h1.text-center')->text());
    }

    /**
     * Tests edit user by owner
     * @group edit-user
     */
    public function testEditUserRoleByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/edit-user', TestDataConfig::OWNER_USER, TestDataConfig::OWNER_PASSWORD, ['username' => 'edit-user']);

        $form = $crawler->selectButton('appbundle_userDto[submit]')->form();
        $form = $this->generateForm(
            $form,
            'appbundle_userDto',
            [
                '[role]' => 0,
            ]
        );
        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertContains('User role successfully edited.', $crawler->filter('div.alert')->text());

        $form = $crawler->selectButton('appbundle_userDto[submit]')->form();
        $form = $this->generateForm(
            $form,
            'appbundle_userDto',
            [
                '[role]' => 1,
            ]
        );
        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertContains('User role successfully edited.', $crawler->filter('div.alert')->text());
    }

    /**
     * Tests edit user by owner
     * @group edit-user
     */
    public function testEditUserRoleByManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/employee', TestDataConfig::MANAGER_USER, TestDataConfig::MANAGER_PASSWORD, ['username' => 'employee']);

        $form = $crawler->selectButton('appbundle_userDto[submit]')->form();
        $form = $this->generateForm(
            $form,
            'appbundle_userDto',
            [
                '[role]' => 0,
            ]
        );
        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertContains('already has', $crawler->filter('div.alert')->text());
    }

    /**
     * @group edit-user
     */
    public function testThatOwnerCanNotEditTheRoleOfAUserThatIsNotPartOfHisHotels()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/ramadaUser1', TestDataConfig::OWNER_USER, TestDataConfig::OWNER_PASSWORD);

        $form = $crawler->selectButton('appbundle_userDto[submit]')->form();
        $form = $this->generateForm(
            $form,
            'appbundle_userDto',
            [
                '[role]' => 0,
            ]
        );
        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertContains('This user is not part of owners hotels.', $crawler->filter('div.alert')->text());
    }

    /**
     * @group edit-user
     */
    public function testThatManagerCanNotEditTheRoleOfAUserThatIsNotPartOfHisHotels()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/ramadaUser1', TestDataConfig::MANAGER_USER, TestDataConfig::MANAGER_PASSWORD, ['username' => 'client']);

        $form = $crawler->selectButton('appbundle_userDto[submit]')->form();
        $form = $this->generateForm(
            $form,
            'appbundle_userDto',
            [
                '[role]' => 0,
            ]
        );
        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertContains('This user is not part of managers hotel.', $crawler->filter('div.alert')->text());
    }

    /**
     * @group user-management
     */
    public function testUserManagementRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::USER_MANAGEMENT);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * @group user-management
     */
    public function testThatClientUserCanNotAccessUserManagementRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::USER_MANAGEMENT, TestDataConfig::CLIENT_USER, TestDataConfig::CLIENT_PASSWORD);
    }

    /**
     * @group user-management
     */
    public function testThatEmployeeUserCanNotAccessUserManagementRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::USER_MANAGEMENT, TestDataConfig::EMPLOYEE_USER, TestDataConfig::EMPLOYEE_PASSWORD);
    }

    /**
     * @group user-management
     */
    public function testUserManagementPageDesignWhenAccessedByManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::USER_MANAGEMENT, TestDataConfig::MANAGER_USER, TestDataConfig::MANAGER_PASSWORD);

        $this->assertCount(0, $crawler->filter('div.search'));
    }

    /**
     * @group user-management
     */
    public function testUserManagementPageDesignWhenAccessedByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::USER_MANAGEMENT, TestDataConfig::OWNER_USER, TestDataConfig::OWNER_PASSWORD);

        $this->assertCount(1, $crawler->filter('div.search'));
    }

    /**
     * @group paginate-and-sort
     */
    public function testUserManagementPaginateAndSortRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::PAGINATE_AND_SORT);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * @group paginate-and-sort
     */
    public function testThatClientUserCanNotAccessUserManagementPaginateAndSortRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::PAGINATE_AND_SORT, TestDataConfig::CLIENT_USER, TestDataConfig::CLIENT_PASSWORD);
    }

    /**
     * @group paginate-and-sort
     */
    public function testThatEmployeeUserCanNotAccessUserManagementPaginateAndSortRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::PAGINATE_AND_SORT, TestDataConfig::EMPLOYEE_USER, TestDataConfig::EMPLOYEE_PASSWORD);
    }

    /**
     * @group paginate-and-sort
     */
    public function testUserManagementPaginateAndSortRouteAccessedByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::PAGINATE_AND_SORT, TestDataConfig::OWNER_USER, TestDataConfig::OWNER_PASSWORD);

        $this->assertCount(1, $crawler->filter('div.error-template'));
        $this->assertContains('Stay out', $crawler->filter('h2')->text());
    }

    /**
     * @group paginate-and-sort
     */
    public function testUserManagementPaginateAndSortRouteAccessedByManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::PAGINATE_AND_SORT, TestDataConfig::MANAGER_USER, TestDataConfig::MANAGER_PASSWORD);

        $this->assertCount(1, $crawler->filter('div.error-template'));
        $this->assertContains('Stay out', $crawler->filter('h2')->text());
    }

    /**
     * @group paginate-and-sort
     */
    public function testPaginateAndSortRouteAccessedWithAjaxByManager()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, TestDataConfig::MANAGER_USER, TestDataConfig::MANAGER_PASSWORD);
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request(
            'GET',
            '/user-management/paginate-and-sort',
            ['type' => 'manager', 'pageNumber' => 1, 'paginate' => 'true'],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertCount(1, $crawler->filter('div.table-paginated'));
    }

    /**
     * @group paginate-and-sort
     */
    public function testPaginateAndSortRouteAccessedWithAjaxByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::PAGINATE_AND_SORT, TestDataConfig::OWNER_USER, TestDataConfig::OWNER_PASSWORD, ['type' => 'owner', 'pageNumber' => 1, 'paginate' => 'true'], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $this->assertCount(1, $crawler->filter('h1'));
    }
}
