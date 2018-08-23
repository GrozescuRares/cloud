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
        $form = $this->generateRegistrationForm($form, $username, $email, '12345', '12345');

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('registration-confirmation', [
                'email' => $email,
            ]))
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

        $form = $this->generateRegistrationForm($form, $username, $email, '11', '1');
        $crawler = $client->submit($form);

        $this->assertContains('This value is', $crawler->filter('div.rel ul li')->text());
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

        $form = $this->generateRegistrationForm($form, $username, $email, '1', '1');
        $crawler = $client->submit($form);

        $this->assertContains('This value is too short. It should have 5 characters or more.', $crawler->filter('div.rel ul li')->text());
    }

    /**
     * Test no password
     */
    public function testNoPassword()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('register'));

        $form = $crawler->selectButton('appbundle_user[submit]')->form();

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';

        $form = $this->generateRegistrationForm($form, $username, $email, '', '');
        $crawler = $client->submit($form);

        $this->assertContains('This value should not be blank', $crawler->filter('div.rel ul li')->text());
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
        $form = $this->generateRegistrationForm($form, $username, $email, '1', '1');

        $crawler = $client->submit($form);

        $this->assertContains('This value is too short. It should have 5 characters or more.', $crawler->filter('div.rel ul li')->text());
    }

    /**
     * @param $form
     * @param $username
     * @param $email
     * @param $firstPassword
     * @param $secondPassword
     * @return mixed
     */
    private function generateRegistrationForm($form, $username, $email, $firstPassword, $secondPassword)
    {
        $form['appbundle_user[username]'] = $username;
        $form['appbundle_user[email]'] = $email;
        $form['appbundle_user[plainPassword][first]'] = $firstPassword;
        $form['appbundle_user[plainPassword][second]'] = $secondPassword;
        $form['appbundle_user[dateOfBirth][day]'] = '1';
        $form['appbundle_user[dateOfBirth][month]'] = '2';
        $form['appbundle_user[dateOfBirth][year]'] = '1950';
        $form['appbundle_user[firstName]'] = $username."FirstName";
        $form['appbundle_user[lastName]'] = $username."LastName";

        return $form;
    }
}
