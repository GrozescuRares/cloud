<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 21.08.2018
 * Time: 10:08
 */

namespace Tests\AppBundle\Controller;

use AppBundle\Enum\RoutesConfig;
use AppBundle\Enum\TestDataConfig;
use Tests\AppBundle\BaseWebTestCase;

/**
 * Class MyAccountControllerTest
 * @package Tests\AppBundle\Controller
 */
class MyAccountControllerTest extends BaseWebTestCase
{
    /**
     * Tests the my-account route with no user logged
     * @group my-account
     */
    public function testMyAccountRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::MY_ACCOUNT);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests accessing my account after user login
     * @group my-account
     */
    public function testAccessingMyAccountRouteAfterLogin()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::MY_ACCOUNT, TestDataConfig::CLIENT_USER, TestDataConfig::CLIENT_PASSWORD);

        $this->assertContains('My account', $crawler->filter('h1.text-center')->text());
    }

    /**
     * Tests the edit-my-account route with no user logged
     * @group edit-my-account
     */
    public function testEditMyAccountRoute()
    {
        $client = static::createClient();
        $client->request('GET', RoutesConfig::EDIT_MY_ACCOUNT);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests a successfully account edit
     * @group edit-my-account
     */
    public function testSuccessfulAccountEdit()
    {
        list($client, $crawler) = $this->accessRoute(RoutesConfig::EDIT_MY_ACCOUNT, TestDataConfig::CLIENT_USER, TestDataConfig::CLIENT_PASSWORD);

        $bio = 'bio'.substr(md5(time()), 0, 6);

        $form = $crawler->selectButton('appbundle_user[submit]')->form();
        $form = $this->generateForm(
            $form,
            'appbundle_user',
            [
                '[bio]' => $bio,
            ]
        );
        $crawler = $client->submit($form);

        $this->assertContains('Your data was saved', $crawler->filter('div.alert')->text());
    }
}
