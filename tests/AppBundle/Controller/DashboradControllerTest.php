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
}
