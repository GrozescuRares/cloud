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

    /**
     * Tests successfully register
     */
    public function testSuccessfullyRegisterFormSubmitWithTokenActivation()
    {
        $client = static:: createClient();

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('register'));

        $form = $crawler->selectButton('appbundle_user[submit]')->form();

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';
        $form['appbundle_user[username]'] = $username;
        $form['appbundle_user[email]'] = $email;
        $form['appbundle_user[plainPassword][first]'] = 'password';
        $form['appbundle_user[plainPassword][second]'] = 'password';
        $form['appbundle_user[dateOfBirth][day]'] = '1';
        $form['appbundle_user[dateOfBirth][month]'] = '2';
        $form['appbundle_user[dateOfBirth][year]'] = '1950';

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('registration-confirmation'))
        );
    }

    /**
     * Tests nonMatching passwords
     */
    public function testNonMatchingPasswords()
    {
        $client = static:: createClient();

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('register'));

        $form = $crawler->selectButton('appbundle_user[submit]')->form();

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';
        $form['appbundle_user[username]'] = $username;
        $form['appbundle_user[email]'] = $email;
        $form['appbundle_user[plainPassword][first]'] = 'passwrd';
        $form['appbundle_user[plainPassword][second]'] = 'password';
        $form['appbundle_user[dateOfBirth][day]'] = '1';
        $form['appbundle_user[dateOfBirth][month]'] = '2';
        $form['appbundle_user[dateOfBirth][year]'] = '1950';

        $crawler = $client->submit($form);

        $this->assertContains('This value is not valid', $crawler->filter('div.rel ul li')->text());
    }


    /**
     * Tests invalid passwords
     */
    public function testInvalidPassword()
    {
        $client = static:: createClient();

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('register'));

        $form = $crawler->selectButton('appbundle_user[submit]')->form();

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';
        $form['appbundle_user[username]'] = $username;
        $form['appbundle_user[email]'] = $email;
        $form['appbundle_user[plainPassword][first]'] = '1';
        $form['appbundle_user[plainPassword][second]'] = '1';
        $form['appbundle_user[dateOfBirth][day]'] = '1';
        $form['appbundle_user[dateOfBirth][month]'] = '2';
        $form['appbundle_user[dateOfBirth][year]'] = '1950';

        $crawler = $client->submit($form);

        $this->assertContains('This value is too short. It should have 5 characters or more.', $crawler->filter('div.rel ul li')->text());
    }

    /**
     * Tests invalid Username
     */
    public function testInvalidUsername()
    {
        $client = static:: createClient();

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('register'));

        $form = $crawler->selectButton('appbundle_user[submit]')->form();

        $username = 'user';
        $email = substr(md5(time()), 0, 6).'@ceva.com';
        $form['appbundle_user[username]'] = $username;
        $form['appbundle_user[email]'] = $email;
        $form['appbundle_user[plainPassword][first]'] = '1';
        $form['appbundle_user[plainPassword][second]'] = '1';
        $form['appbundle_user[dateOfBirth][day]'] = '1';
        $form['appbundle_user[dateOfBirth][month]'] = '2';
        $form['appbundle_user[dateOfBirth][year]'] = '1950';

        $crawler = $client->submit($form);

        $this->assertContains('This value is too short. It should have 5 characters or more.', $crawler->filter('div.rel ul li')->text());
    }
}
