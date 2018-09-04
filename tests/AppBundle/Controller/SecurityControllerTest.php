<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 13.08.2018
 * Time: 16:46
 */

namespace Tests\AppBundle\Controller;

use AppBundle\Enum\RoutesConfig;
use Tests\AppBundle\BaseWebTestCase;

/**
 * Class SecurityControllerTest
 * @package Tests\AppBundle\Controller
 */
class SecurityControllerTest extends BaseWebTestCase
{
    /**
     * Tests the login route
     * @group login
     */
    public function testLoginRoute()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', RoutesConfig::LOGIN);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Welcome to Hotel Management System', $crawler->filter('h1.text-center')->text());
    }

    /**
     * Tests successfully log in
     * @group login
     */
    public function testSuccessfullyLogInFormSubmit()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', RoutesConfig::LOGIN);

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'client', '12345');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));
    }

    /**
     * Tests bad credentials log in
     * @group login
     */
    public function testBadCredentials()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', RoutesConfig::LOGIN);

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'noOne', '12345');
        $client->submit($form);

        $this->assertRegExp('/\/login$/', $client->getResponse()->headers->get('location'));

        $crawler = $client->followRedirect();

        $this->assertContains('Bad credentials', $crawler->filter('div.alert')->text());
    }

    /**
     * Tests log in with inactive account
     * @group login
     */
    public function testLoginWithInactiveAccount()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', RoutesConfig::LOGIN);

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'testInactive', '12345');
        $client->submit($form);

        $this->assertRegExp('/\/login$/', $client->getResponse()->headers->get('location'));

        $crawler = $client->followRedirect();

        $this->assertContains('This account is not active !', $crawler->filter('div.alert')->text());
    }

    /**
     * Tests that logged user can't access the login page
     * @group security
     */
    public function testThatLoggedUserCanNotAccessLogInPage()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', RoutesConfig::LOGIN);

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'client', '12345');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('dashboard'))
        );
    }

    /**
     * Tests that logged user can't access the registration page
     * @group security
     */
    public function testThatLoggedUserCanNotAccessRegisterPage()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::REGISTER, 'client', '12345');

        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('dashboard'))
        );
    }

    /**
     * Tests that logged user can't access the activate-account page
     * @group security
     */
    public function testThatLoggedUserCanNotAccessActivateAccountPage()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::ACTIVATE_ACCOUNT.'/dscsdcddsccds', 'client', '12345');

        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('dashboard'))
        );
    }
}
