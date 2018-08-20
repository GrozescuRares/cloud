<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 16.08.2018
 * Time: 16:44
 */

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DashboradControllerTest
 * @package Tests\AppBundle\Controller
 */
class DashboradControllerTest extends WebTestCase
{
    /**
     * Tests the dashboard route with no user logged
     */
    public function testDashboardRoute()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests dashboard page design after owner log in
     */
    public function testDashboardPageDesignAfterOwnerLogIn()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'owner', 'owner');
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );

        $crawler = $client->followRedirect();

        $this->assertCount(5, $crawler->filter('#navigation li'));
    }

    /**
     * Tests dashboard page design after manager log in
     */
    public function testDashboardPageDesignAfterManagerLogIn()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'manager1', 'manager');
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );

        $crawler = $client->followRedirect();

        $this->assertCount(5, $crawler->filter('#navigation li'));
    }

    /**
     * Tests dashboard page design after employee log in
     */
    public function testDashboardPageDesignAfterEmployeeLogIn()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'employee', '12345');
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );

        $crawler = $client->followRedirect();

        $this->assertCount(3, $crawler->filter('#navigation li'));
    }

    /**
     * Tests dashboard page design after manager log in
     */
    public function testDashboardPageDesignAfterClientLogIn()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'client', '12345');
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );

        $crawler = $client->followRedirect();

        $this->assertCount(3, $crawler->filter('#navigation li'));
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
