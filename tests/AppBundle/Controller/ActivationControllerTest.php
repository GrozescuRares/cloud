<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 16.08.2018
 * Time: 16:41
 */

namespace Tests\AppBundle\Controller;

use AppBundle\Enum\RoutesConfig;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ActivationControllerTest
 * @package Tests\AppBundle\Controller
 */
class ActivationControllerTest extends WebTestCase
{
    /**
     * Tests the activate-account route
     * @group activation
     */
    public function testActivateAccountRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::ACTIVATE_ACCOUNT.'/dskhnkfdndn');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests invalid token
     * @group activation
     */
    public function testInvalidToken()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', RoutesConfig::ACTIVATE_ACCOUNT.'/dsgfjdfgd');

        $this->assertContains('Oops', $crawler->filter('h1')->text());
    }
}
