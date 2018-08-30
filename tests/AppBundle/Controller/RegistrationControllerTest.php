<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 13.08.2018
 * Time: 17:23
 */

namespace Tests\AppBundle\Controller;

use AppBundle\Enum\RoutesConfig;
use Tests\AppBundle\BaseWebTestCase;

/**
 * Class SecurityControllerTest
 * @package Tests\AppBundle\Controller
 */
class RegistrationControllerTest extends BaseWebTestCase
{
    /**
     * Tests the register route
     * @group register
     */
    public function testRegisterRoute()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', RoutesConfig::REGISTER);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Client Registration', $crawler->filter('h1.text-center')->text());
    }

    /**
     * Tests successfully register
     * @group register
     */
    public function testSuccessfullyRegisterFormSubmitWithTokenActivation()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', RoutesConfig::REGISTER);

        $form = $crawler->selectButton('appbundle_user[submit]')->form();

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';

        $form = $this->generateForm(
            $form,
            'appbundle_user',
            [
                '[username]' => $username,
                '[email]' => $email,
                '[plainPassword][first]' => '12345',
                '[plainPassword][second]' => '12345',
                '[dateOfBirth][day]' => '1',
                '[dateOfBirth][month]' => '2',
                '[dateOfBirth][year]' => '1950',
                '[firstName]' => $username."FirstName",
                '[lastName]' => $username."LastName",
            ]
        );
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect(
                $client->getContainer()->get('router')->generate(
                    'registration-confirmation',
                    [
                        'email' => $email,
                    ]
                )
            )
        );
    }

    /**
     * Tests nonMatching passwords
     * @group register
     */
    public function testNonMatchingPasswords()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', RoutesConfig::REGISTER);

        $form = $crawler->selectButton('appbundle_user[submit]')->form();

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';
        $form = $this->generateForm(
            $form,
            'appbundle_user',
            [
                '[username]' => $username,
                '[email]' => $email,
                '[plainPassword][first]' => '11',
                '[plainPassword][second]' => '1',
                '[dateOfBirth][day]' => '1',
                '[dateOfBirth][month]' => '2',
                '[dateOfBirth][year]' => '1950',
                '[firstName]' => $username."FirstName",
                '[lastName]' => $username."LastName",
            ]
        );
        $crawler = $client->submit($form);

        $this->assertContains('This value is', $crawler->filter('div.rel ul li')->text());
    }


    /**
     * Tests invalid passwords
     * @group register
     */
    public function testInvalidPassword()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', RoutesConfig::REGISTER);

        $form = $crawler->selectButton('appbundle_user[submit]')->form();

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';

        $form = $this->generateForm(
            $form,
            'appbundle_user',
            [
                '[username]' => $username,
                '[email]' => $email,
                '[plainPassword][first]' => '1',
                '[plainPassword][second]' => '1',
                '[dateOfBirth][day]' => '1',
                '[dateOfBirth][month]' => '2',
                '[dateOfBirth][year]' => '1950',
                '[firstName]' => $username."FirstName",
                '[lastName]' => $username."LastName",
            ]
        );
        $crawler = $client->submit($form);

        $this->assertContains(
            'This value is too short. It should have 5 characters or more.',
            $crawler->filter('div.rel ul li')->text()
        );
    }

    /**
     * Test no password
     * @group register
     */
    public function testNoPassword()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', RoutesConfig::REGISTER);

        $form = $crawler->selectButton('appbundle_user[submit]')->form();

        $username = 'user'.substr(md5(time()), 0, 6);
        $email = substr(md5(time()), 0, 6).'@ceva.com';

        $form = $this->generateForm(
            $form,
            'appbundle_user',
            [
                '[username]' => $username,
                '[email]' => $email,
                '[plainPassword][first]' => '',
                '[plainPassword][second]' => '',
                '[dateOfBirth][day]' => '1',
                '[dateOfBirth][month]' => '2',
                '[dateOfBirth][year]' => '1950',
                '[firstName]' => $username."FirstName",
                '[lastName]' => $username."LastName",
            ]
        );
        $crawler = $client->submit($form);

        $this->assertContains('This value should not be blank', $crawler->filter('div.rel ul li')->text());
    }

    /**
     * Tests invalid Username
     * @group register
     */
    public function testInvalidUsername()
    {
        $client = static:: createClient();
        $crawler = $client->request('GET', RoutesConfig::REGISTER);

        $form = $crawler->selectButton('appbundle_user[submit]')->form();

        $username = 'user';
        $email = substr(md5(time()), 0, 6).'@ceva.com';
        $form = $this->generateForm(
            $form,
            'appbundle_user',
            [
                '[username]' => $username,
                '[email]' => $email,
                '[plainPassword][first]' => '1',
                '[plainPassword][second]' => '1',
                '[dateOfBirth][day]' => '1',
                '[dateOfBirth][month]' => '2',
                '[dateOfBirth][year]' => '1950',
                '[firstName]' => $username."FirstName",
                '[lastName]' => $username."LastName",
            ]
        );

        $crawler = $client->submit($form);

        $this->assertContains(
            'This value is too short. It should have 5 characters or more.',
            $crawler->filter('div.rel ul li')->text()
        );
    }
}
