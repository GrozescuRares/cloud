<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 16.08.2018
 * Time: 16:47
 */

namespace Tests\AppBundle\Flow;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class RegistrationAndActivationTest
 * @package Tests\AppBundle\Flow
 */
class RegistrationAndActivationTest extends WebTestCase
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
        $form['appbundle_user[firstName]'] = $username."FirstName";
        $form['appbundle_user[lastName]'] = $username."LastName";

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('registration-confirmation'))
        );

        /*
         * Activation
         */

        $client->followRedirect();

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('activate-account', [
            'activationToken' => md5($username).md5($email),
        ]));

        $this->assertContains('Your account is active now', $crawler->filter('h1')->text());

        /*
         * Log in
         */

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();

        $form['_username'] = $username;
        $form['_password'] = 'password';

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );
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
        $form['appbundle_user[firstName]'] = $username."FirstName";
        $form['appbundle_user[lastName]'] = $username."LastName";

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('registration-confirmation'))
        );

        /*
         * Log in - fail
         */

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();

        $form['_username'] = $username;
        $form['_password'] = 'password';

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('login'))
        );

        /*
         * Activation
         */

        $client->followRedirect();

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('activate-account', [
            'activationToken' => md5($username).md5($email),
        ]));

        $this->assertContains('Your account is active now', $crawler->filter('h1')->text());

        /*
         * Log in
         */

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();

        $form['_username'] = $username;
        $form['_password'] = 'password';

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );
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
        $form['appbundle_user[firstName]'] = $username."FirstName";
        $form['appbundle_user[lastName]'] = $username."LastName";

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect($client->getContainer()->get('router')->generate('registration-confirmation'))
        );

        /*
         * Activation
         */

        $client->followRedirect();

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('activate-account', [
            'activationToken' => md5($username).md5($email),
        ]));

        $this->assertContains('Your account is active now', $crawler->filter('h1')->text());

        /*
         * Activation fail
         */

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('activate-account', [
            'activationToken' => md5($username).md5($email),
        ]));

        $this->assertContains('Oops', $crawler->filter('h1')->text());

        /*
         * Log in
         */

        $crawler = $client->request('GET', $client->getContainer()->get('router')->generate('login'));

        $form = $crawler->selectButton('submit')->form();

        $form['_username'] = $username;
        $form['_password'] = 'password';

        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost'.$client->getContainer()->get('router')->generate('dashboard'))
        );
    }
}
