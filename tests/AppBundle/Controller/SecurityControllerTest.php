<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 13.08.2018
 * Time: 16:46
 */

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest
 * @package Tests\AppBundle\Controller
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Tests the login route
     */
    public function testLoginRoute()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Welcome to Hotel Management System', $crawler->filter('h1.text-center')->text());
    }

    /**
     * Tests successfully log in
     */
    public function testSuccessfullyLogInFormSubmit()
    {
        $client = static:: createClient();

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'client', '12345');
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );
    }

    /**
     * Tests bad credentials log in
     */
    public function testBadCredentials()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'noOne', '12345');
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('login'))
        );

        $crawler = $client->followRedirect();

        $this->assertContains('Bad credentials', $crawler->filter('div.alert')->text());
    }

    /**
     * Tests log in with inactive account
     */
    public function testLoginWithInactiveAccount()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'testInactive', '12345');
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('login'))
        );

        $crawler = $client->followRedirect();

        $this->assertContains('This account is not active !', $crawler->filter('div.alert')->text());
    }

    /**
     * Tests that logged user can't access the login page
     */
    public function testThatLoggedUserCanNotAccessLogInPage()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'client', '12345');
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );

        $client->followRedirect();
        $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('dashboard'))
        );
    }

    /**
     * Tests that logged user can't access the registration page
     */
    public function testThatLoggedUserCanNotAccessRegisterPage()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'client', '12345');
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );

        $client->followRedirect();

        $client->request('GET', $client->getContainer()->get('router')->generate('register'));
        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('dashboard'))
        );
    }

    /**
     * Tests that logged user can't access the activate-account page
     */
    public function testThatLoggedUserCanNotAccessActivateAccountPage()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'client', '12345');
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );

        $client->followRedirect();

        $client->request('GET', $client->getContainer()->get('router')->generate('activate-account', ['activationToken' => 'dfefefe']));
        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('dashboard'))
        );
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
}
