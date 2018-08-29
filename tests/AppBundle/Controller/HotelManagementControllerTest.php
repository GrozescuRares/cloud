<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 16:59
 */

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\BaseWebTestCase;

/**
 * Class HotelManagementControllerTest
 * @package Tests\AppBundle\Controller
 */
class HotelManagementControllerTest extends BaseWebTestCase
{
    /**
     *
     */
    public function testAddRoomRoute()
    {
        $client = static::createClient();
        $client->request('GET', '/hotel-management/add-room');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     *
     */
    public function testThatClientUserCanNotAccessAddRoomRoute()
    {
        $this->userCanNotAccessRoute('/hotel-management/add-room', 'rares', 'handstand');
    }

    /**
     *
     */
    public function testThatEmployeeUserCanNotAccessAddRoomRoute()
    {
        $this->userCanNotAccessRoute('/hotel-management/add-room', 'employee', '12345');
    }

    /**
     *
     */
    public function testThatManagerUserCanNotAccessAddRoomRoute()
    {
        $this->userCanNotAccessRoute('/hotel-management/add-room', 'manager1', 'manager');
    }

    /**
     *
     */
    public function testSuccessfullyAddRoomByOwner()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'owner', 'owner');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request('GET', '/hotel-management/add-room');

        $form = $crawler->selectButton('appbundle_roomDto[save]')->form();
        $form = $this->generateForm($form, 'appbundle_roomDto', [
            '[hotel]' => 0,
            '[capacity]' => 0,
            '[price]' => '1000',
            '[smoking]' => 0,
            '[pet]' => 1,
        ]);
        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertContains('The room was successfully added.', $crawler->filter('div.alert')->text());
    }

    /**
     *
     */
    public function testAddRoomWithInvalidPrice()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'owner', 'owner');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request('GET', '/hotel-management/add-room');

        $form = $crawler->selectButton('appbundle_roomDto[save]')->form();
        $form = $this->generateForm($form, 'appbundle_roomDto', [
            '[hotel]' => 0,
            '[capacity]' => 0,
            '[price]' => 'sdgdsvgs',
            '[smoking]' => 0,
            '[pet]' => 1,
        ]);
        $crawler = $client->submit($form);

        $this->assertContains('This is not a valid value for price.', $crawler->filter('div.rel ul li')->text());
    }

    /**
     *
     */
    public function testAddRoomWithNoHotelSelected()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'owner', 'owner');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request('GET', '/hotel-management/add-room');

        $form = $crawler->selectButton('appbundle_roomDto[save]')->form();
        $form = $this->generateForm($form, 'appbundle_roomDto', [
            '[capacity]' => 0,
            '[price]' => 'sdgdsvgs',
            '[smoking]' => 0,
            '[pet]' => 1,
        ]);
        $crawler = $client->submit($form);

        $this->assertContains('Please choose a hotel.', $crawler->filter('div.styled-input ul li')->text());
    }

    /**
     *
     */
    public function testAddRoomWithNoCapacitySelected()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'owner', 'owner');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request('GET', '/hotel-management/add-room');

        $form = $crawler->selectButton('appbundle_roomDto[save]')->form();
        $form = $this->generateForm($form, 'appbundle_roomDto', [
            '[hotel]' => 0,
            '[price]' => 'sdgdsvgs',
            '[smoking]' => 0,
            '[pet]' => 1,
        ]);
        $crawler = $client->submit($form);

        $this->assertContains('Please choose capacity.', $crawler->filter('div.styled-input ul li')->text());
    }

}
