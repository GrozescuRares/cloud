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

        $form['_username'] = 'rares';
        $form['_password'] = 'handstand';

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

        $form['_username'] = 'rares';
        $form['_password'] = 'hand';

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('login'))
        );

        $crawler = $client->followRedirect();

        $this->assertContains('Bad credentials', $crawler->filter('div.alert')->text());
    }

}
