<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 21.08.2018
 * Time: 10:08
 */

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class MyAccountControllerTest
 * @package Tests\AppBundle\Controller
 */
class MyAccountControllerTest extends WebTestCase
{
    /**
     * Tests the my-account route with no user logged
     */
    public function testMyAccountRoute()
    {
        $client = static::createClient();
        $client->request('GET', '/my-account');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests accessing my account after user login
     */
    public function testAccessingMyAccountRouteAfterLogin()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'client', '12345');
        $client->submit($form);

        $client->followRedirect();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('my-account'));

        $this->assertContains('My account', $crawler->filter('h1.text-center')->text());
    }

    /**
     * Tests the edit-my-account route with no user logged
     */
    public function testEditMyAccountRoute()
    {
        $client = static::createClient();
        $client->request('GET', '/edit-my-account');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests a successfully account edit
     */
    public function testSuccessfulAccountEdit()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, 'client', '12345');
        $client->submit($form);

        $client->followRedirect();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('edit-my-account'));

        $bio = 'bio'.substr(md5(time()), 0, 6);

        $form = $crawler->selectButton('appbundle_user[submit]')->form();
        $form = $this->generateRegistrationForm($form, $bio);
        $crawler = $client->submit($form);

        $this->assertContains('Your data was saved', $crawler->filter('div.alert')->text());
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

    /**
     * @param $form
     * @param string $bio
     * @return mixed
     */
    private function generateRegistrationForm($form, $bio)
    {
        $form['appbundle_user[bio]'] = $bio;

        return $form;
    }
}