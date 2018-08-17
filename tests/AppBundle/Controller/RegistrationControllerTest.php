<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 13.08.2018
 * Time: 17:23
 */

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest
 * @package Tests\AppBundle\Controller
 */
class RegistrationControllerTest extends WebTestCase
{
    /**
     * Tests the register route
     */
    public function testRegisterRoute()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Client Registration', $crawler->filter('h1.text-center')->text());
    }
}
