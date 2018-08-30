<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 16.08.2018
 * Time: 16:47
 */

namespace Tests\AppBundle\Flow;

use AppBundle\Enum\RoutesConfig;
use Tests\AppBundle\BaseWebTestCase;

/**
 * Class RegistrationAndActivationTest
 * @package Tests\AppBundle\Flow
 */
class RegistrationAndActivationTest extends BaseWebTestCase
{
    /**
     * Tests successfully registration-activation-login flow
     */
    public function testSuccesfullyRegistrationAccountActivationAndLogIn()
    {
        /*
         * Registration
         */

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
                '[plainPassword][first]' => 'password',
                '[plainPassword][second]' => 'password',
                '[dateOfBirth][day]' => '1',
                '[dateOfBirth][month]' => '2',
                '[dateOfBirth][year]' => '1950',
                '[firstName]' => $username."FirstName",
                '[lastName]' => $username."LastName",
            ]
        );
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect(RoutesConfig::REGISTRATION_CONFIRMATION.'/'.$email)
        );

        /*
         * Activation
         */

        $client->followRedirect();
        $crawler = $client->request('GET', RoutesConfig::ACTIVATE_ACCOUNT.'/'.md5($username).md5($email));

        $this->assertContains('Your account is active now', $crawler->filter('h1')->text());

        /*
         * Log in
         */

        $crawler = $client->request('GET', RoutesConfig::LOGIN);

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, $username, 'password');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));
    }


    /**
     * Flow : registration - try to log in and fail (account is not activated) - activate account - log in
     */
    public function testRegistrationFailedLoginAccountActivationAndSuccessfullyLogIn()
    {
        /*
         * Registration
         */

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
                '[plainPassword][first]' => 'password',
                '[plainPassword][second]' => 'password',
                '[dateOfBirth][day]' => '1',
                '[dateOfBirth][month]' => '2',
                '[dateOfBirth][year]' => '1950',
                '[firstName]' => $username."FirstName",
                '[lastName]' => $username."LastName",
            ]
        );
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect(RoutesConfig::REGISTRATION_CONFIRMATION.'/'.$email)
        );

        /*
         * Log in - fail
         */

        $crawler = $client->request('GET', RoutesConfig::LOGIN);

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, $username, 'password');
        $client->submit($form);

        $this->assertRegExp('/\/login$/', $client->getResponse()->headers->get('location'));

        /*
         * Activation
         */

        $client->followRedirect();
        $crawler = $client->request('GET', RoutesConfig::ACTIVATE_ACCOUNT.'/'.md5($username).md5($email));


        $this->assertContains('Your account is active now', $crawler->filter('h1')->text());

        /*
         * Log in
         */

        $crawler = $client->request('GET', RoutesConfig::LOGIN);

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, $username, 'password');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));
    }

    /**
     * Tests registration-activation- activation (fail) - login
     */
    public function testRegistrationActivationAnotherActivationAndLogIn()
    {
        /*
         * Registration
         */

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
                '[plainPassword][first]' => 'password',
                '[plainPassword][second]' => 'password',
                '[dateOfBirth][day]' => '1',
                '[dateOfBirth][month]' => '2',
                '[dateOfBirth][year]' => '1950',
                '[firstName]' => $username."FirstName",
                '[lastName]' => $username."LastName",
            ]
        );
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect(RoutesConfig::REGISTRATION_CONFIRMATION.'/'.$email)
        );

        /*
         * Activation
         */

        $client->followRedirect();
        $crawler = $client->request('GET', RoutesConfig::ACTIVATE_ACCOUNT.'/'.md5($username).md5($email));

        $this->assertContains('Your account is active now', $crawler->filter('h1')->text());

        /*
         * Activation fail
         */

        $crawler = $client->request('GET', RoutesConfig::ACTIVATE_ACCOUNT.'/'.md5($username).md5($email));

        $this->assertContains('Oops', $crawler->filter('h1')->text());

        /*
         * Log in
         */

        $crawler = $client->request('GET', RoutesConfig::LOGIN);

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, $username, 'password');
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));
    }
}
