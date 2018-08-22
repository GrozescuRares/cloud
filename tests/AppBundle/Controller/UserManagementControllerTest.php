<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 13:52
 */

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserManagementControllerTest
 * @package Tests\AppBundle\Controller
 */
class UserManagementControllerTest extends WebTestCase
{
    /**
     * Tests the add-user route with no user logged
     */
    public function testAddUserRoute()
    {
        $client = static::createClient();
        $client->request('GET', '/user-management/add-user');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests that client user can not access add-user route
     */
    public function testThatClientUserCanNotAccessAddUserRoute()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'rares', 'handstand');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $client->request('GET', '/user-management/add-user');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests that employee user can not access add-user route
     */
    public function testThatEmployeeUserCanNotAccessAddUserRoute()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'employee', '12345');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $client->request('GET', '/user-management/add-user');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests page design when accessed by owner
     */
    public function testPageDesignWhenAccessedByOwner()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'owner', 'owner');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request('GET', '/user-management/add-user');

        $this->assertEquals(5, $crawler->filter('div.second')->children()->count());
    }

    /**
     * Tests page design when accessed by manager
     */
    public function testPageDesignWhenAccessedByManager()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'manager1', 'manager');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request('GET', '/user-management/add-user');

        $this->assertEquals(4, $crawler->filter('div.second')->children()->count());
    }

    /**
     * Tests that owner can successfully add user
     */
    public function testSuccessfullyAddUserByOwner()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'owner', 'owner');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request('GET', '/user-management/add-user');

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';

        $form = $crawler->selectButton('appbundle_user[submit]')->form();
        $form = $this->generateAddUserForm($form, $username, $email, '12345', '12345');
        $client->submit($form);

        $this->assertRegExp('/\/user-management\/add-user$/', $client->getResponse()->headers->get('location'));

        $crawler = $client->followRedirect();

        $this->assertContains('Add user form successfully submitted. Thank you !', $crawler->filter('div.alert')->text());
    }

    /**
     * Tests that manager can successfully add user
     */
    public function testSuccessfullyAddUserByManager()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'manager1', 'manager');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request('GET', '/user-management/add-user');

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';

        $form = $crawler->selectButton('appbundle_user[submit]')->form();
        $form = $this->generateAddUserForm($form, $username, $email, '12345', '12345');
        $client->submit($form);

        $this->assertRegExp('/\/user-management\/add-user$/', $client->getResponse()->headers->get('location'));

        $crawler = $client->followRedirect();

        $this->assertContains('Add user form successfully submitted. Thank you !', $crawler->filter('div.alert')->text());
    }

    /**
     * @param $form
     * @param $username
     * @param $password
     * @return mixed
     */
    private function generateLoginForm($form, $username, $password)
    {
        $form['_username'] = $username;
        $form['_password'] = $password;

        return $form;
    }

    /**
     * @param $form
     * @param $username
     * @param $email
     * @param $firstPassword
     * @param $secondPassword
     * @return mixed
     */
    private function generateAddUserForm($form, $username, $email, $firstPassword, $secondPassword)
    {
        $form['appbundle_user[username]'] = $username;
        $form['appbundle_user[email]'] = $email;
        $form['appbundle_user[plainPassword][first]'] = $firstPassword;
        $form['appbundle_user[plainPassword][second]'] = $secondPassword;
        $form['appbundle_user[dateOfBirth][day]'] = '1';
        $form['appbundle_user[dateOfBirth][month]'] = '2';
        $form['appbundle_user[dateOfBirth][year]'] = '1950';
        $form['appbundle_user[firstName]'] = $username."FirstName";
        $form['appbundle_user[lastName]'] = $username."LastName";

        return $form;
    }
}
