<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 13:52
 */

namespace Tests\AppBundle\Controller;

use AppBundle\Enum\RoutesConfig;
use Tests\AppBundle\BaseWebTestCase;

/**
 * Class UserManagementControllerTest
 * @package Tests\AppBundle\Controller
 */
class UserManagementControllerTest extends BaseWebTestCase
{
    /**
     * Tests the add-user route with no user logged
     */
    public function testAddUserRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::ADD_USER);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests that client user can not access add-user route
     */
    public function testThatClientUserCanNotAccessAddUserRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::ADD_USER, 'rares', 'handstand');
    }

    /**
     * Tests that employee user can not access add-user route
     */
    public function testThatEmployeeUserCanNotAccessAddUserRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::ADD_USER, 'employee', '12345');
    }

    /**
     * Tests page design when accessed by owner
     */
    public function testAddUserPageDesignWhenAccessedByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::ADD_USER, 'owner', 'owner');

        $this->assertEquals(5, $crawler->filter('div.second')->children()->count());
    }

    /**
     * Tests page design when accessed by manager
     */
    public function testAddUserPageDesignWhenAccessedByManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::ADD_USER, 'manager1', 'manager');

        $this->assertEquals(4, $crawler->filter('div.second')->children()->count());
    }

    /**
     * Tests that owner can successfully add user
     */
    public function testSuccessfullyAddUserByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::ADD_USER, 'owner', 'owner');

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
                '[firstName]' => $username."FirstName",
                '[lastName]' => $username."LastName",
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
     */
    public function testSuccessfullyAddUserByManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::ADD_USER, 'manager1', 'manager');

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
                '[firstName]' => $username."FirstName",
                '[lastName]' => $username."LastName",
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
     */
    public function testEditUserRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::EDIT_USER.'/username');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests that client user can not access edit-user route
     */
    public function testThatClientUserCanNotAccessEditUserRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::EDIT_USER.'/username', 'rares', 'handstand', ['username' => 'username']);
    }

    /**
     * Tests that employee user can not access edit-user route
     */
    public function testThatEmployeeUserCanNotAccessEditUserRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::EDIT_USER.'/username', 'employee', '12345', ['username' => 'username']);
    }

    /**
     * Tests page design when accessed by owner
     */
    public function testEditUserPageDesignWhenAccessedByOwnerOrManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/username', 'owner', 'owner', ['username' => 'username']);

        $this->assertContains('Edit', $crawler->filter('h1.text-center')->text());
    }

    /**
     * Tests edit user by owner
     */
    public function testEditUserRoleByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/edit-user', 'owner', 'owner', ['username' => 'edit-user']);

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
     */
    public function testEditUserRoleByManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/employee', 'manager1', 'manager', ['username' => 'employee']);

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
     *
     */
    public function testThatOwnerCanNotEditTheRoleOfAClient()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/rares', 'owner', 'owner', ['username' => 'rares']);

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

        $this->assertContains('Can not edit users with', $crawler->filter('div.alert')->text());
    }

    /**
     *
     */
    public function testThatManagerCanNotEditTheRoleOfAClient()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/rares', 'manager1', 'manager', ['username' => 'rares']);

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

        $this->assertContains('Can not edit users with', $crawler->filter('div.alert')->text());
    }

    /**
     *
     */
    public function testThatOwnerCanNotEditTheRoleOfAUserThatIsNotPartOfHisHotels()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/client', 'owner', 'owner', ['username' => 'client']);

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
     *
     */
    public function testThatManagerCanNotEditTheRoleOfAUserThatIsNotPartOfHisHotels()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_USER.'/client', 'manager1', 'manager', ['username' => 'client']);

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
     *
     */
    public function testUserManagementRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::USER_MANAGEMENT);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     *
     */
    public function testThatClientUserCanNotAccessUserManagementRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::USER_MANAGEMENT, 'rares', 'handstand');
    }

    /**
     *
     */
    public function testThatEmployeeUserCanNotAccessUserManagementRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::USER_MANAGEMENT, 'employee', '12345');
    }

    /**
     *
     */
    public function testUserManagementPageDesignWhenAccessedByManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::USER_MANAGEMENT, 'manager1', 'manager');

        $this->assertCount(0, $crawler->filter('div.search'));
    }

    /**
     *
     */
    public function testUserManagementPageDesignWhenAccessedByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::USER_MANAGEMENT, 'owner', 'owner');

        $this->assertCount(1, $crawler->filter('div.search'));
    }

    /**
     *
     */
    public function testUserManagementPaginateAndSortRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::PAGINATE_AND_SORT);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     *
     */
    public function testThatClientUserCanNotAccessUserManagementPaginateAndSortRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::PAGINATE_AND_SORT, 'rares', 'handstand');
    }

    /**
     *
     */
    public function testThatEmployeeUserCanNotAccessUserManagementPaginateAndSortRoute()
    {
        $this->userCanNotAccessRoute(RoutesConfig::PAGINATE_AND_SORT, 'employee', '12345');
    }

    /**
     *
     */
    public function testUserManagementPaginateAndSortRouteAccessedByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::PAGINATE_AND_SORT, 'owner', 'owner');

        $this->assertCount(1, $crawler->filter('div.error-template'));
        $this->assertContains('Stay out', $crawler->filter('h2')->text());
    }

    /**
     *
     */
    public function testUserManagementPaginateAndSortRouteAccessedByManager()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::PAGINATE_AND_SORT, 'manager1', 'manager');

        $this->assertCount(1, $crawler->filter('div.error-template'));
        $this->assertContains('Stay out', $crawler->filter('h2')->text());
    }

    /**
     *
     */
    public function testPaginateAndSortRouteAccessedWithAjaxByManager()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'manager1', 'manager');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request(
            'GET',
            '/user-management/paginate-and-sort',
            ['type' => 'owner', 'pageNumber' => 2, 'paginate' => 'true'],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertCount(1, $crawler->filter('div.table-paginated'));
    }

    /**
     *
     */
    public function testPaginateAndSortRouteAccessedWithAjaxByOwner()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::PAGINATE_AND_SORT, 'owner', 'owner', ['type' => 'manager', 'pageNumber' => 2, 'paginate' => 'true'], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $this->assertCount(1, $crawler->filter('div.table-paginated'));
    }
}
