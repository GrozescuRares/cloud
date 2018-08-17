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

        $form['_username'] = 'owner';
        $form['_password'] = 'owner';

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

        $form['_username'] = 'manager1';
        $form['_password'] = 'manager';

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

        $form['_username'] = 'employee';
        $form['_password'] = '12345';

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

        $form['_username'] = 'client';
        $form['_password'] = '12345';

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );

        $crawler = $client->followRedirect();

        $this->assertCount(3, $crawler->filter('#navigation li'));
    }
}
