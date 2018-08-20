<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 16.08.2018
 * Time: 16:41
 */

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ActivationControllerTest
 * @package Tests\AppBundle\Controller
 */
class ActivationControllerTest extends WebTestCase
{
    /**
     * Tests the activate-account route
     */
    public function testActivateAccountRoute()
    {
        $client = static::createClient();
        $client->request('GET', '/activate-account/dskhnkfdndn');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests invalid token
     */
    public function testInvalidToken()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('activate-account', [
            'activationToken' => 'dfsgdyjsghdkjbhvdfkbhfh',
        ]));

        $this->assertContains('Oops', $crawler->filter('h1')->text());
    }
}
