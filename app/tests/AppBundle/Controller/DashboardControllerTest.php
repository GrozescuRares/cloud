<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 16.08.2018
 * Time: 16:44
 */

namespace Tests\AppBundle\Controller;

use AppBundle\Enum\RoutesConfig;
use AppBundle\Enum\TestDataConfig;
use Tests\AppBundle\BaseWebTestCase;

/**
 * Class DashboradControllerTest
 * @package Tests\AppBundle\Controller
 */
class DashboardControllerTest extends BaseWebTestCase
{
    /**
     * Tests the dashboard route with no user logged
     * @group dashboard
     */
    public function testDashboardRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::DASHBOARD);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests dashboard page design after owner log in
     * @group dashboard
     */
    public function testDashboardPageDesignAfterOwnerLogIn()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::DASHBOARD, TestDataConfig::OWNER_USER, TestDataConfig::OWNER_PASSWORD);

        $this->assertCount(5, $crawler->filter('#navigation li'));
    }

    /**
     * Tests dashboard page design after manager log in
     * @group dashboard
     */
    public function testDashboardPageDesignAfterManagerLogIn()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::DASHBOARD, TestDataConfig::MANAGER_USER, TestDataConfig::MANAGER_PASSWORD);

        $this->assertCount(5, $crawler->filter('#navigation li'));
    }

    /**
     * Tests dashboard page design after employee log in
     * @group dashboard
     */
    public function testDashboardPageDesignAfterEmployeeLogIn()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::DASHBOARD, TestDataConfig::EMPLOYEE_USER, TestDataConfig::EMPLOYEE_PASSWORD);

        $this->assertCount(2, $crawler->filter('#navigation li'));
    }

    /**
     * Tests dashboard page design after manager log in
     * @group dashboard
     */
    public function testDashboardPageDesignAfterClientLogIn()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::DASHBOARD, TestDataConfig::CLIENT_USER, TestDataConfig::CLIENT_PASSWORD);

        $this->assertCount(3, $crawler->filter('#navigation li'));
    }
}
