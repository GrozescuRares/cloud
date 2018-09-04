<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 17:02
 */

namespace Tests\AppBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class BaseWebTestCase
 * @package Tests\AppBundle
 */
class BaseWebTestCase extends WebTestCase
{
    /**
     * @param mixed $route
     * @param mixed $username
     * @param mixed $password
     * @param mixed $parameters
     */
    public function userCanNotAccessRoute($route, $username, $password, $parameters = [])
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, $username, $password);
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $client->request('GET', $route, $parameters);

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * @param mixed $form
     * @param mixed $entity
     * @param array $fieldsAndValues
     * @return mixed
     */
    public function generateForm($form, $entity, array $fieldsAndValues)
    {

        foreach ($fieldsAndValues as $field => $value) {
            $form[$entity.$field] = $value;
        }

        return $form;
    }

    /**
     * @param mixed $form
     * @param mixed $username
     * @param mixed $password
     * @return mixed
     */
    public function generateLoginForm($form, $username, $password)
    {
        $form['_username'] = $username;
        $form['_password'] = $password;

        return $form;
    }

    /**
     * @param mixed $route
     * @param mixed $username
     * @param mixed $password
     * @param mixed $parameters
     * @param mixed $headers
     *
     * @return array
     */
    public function accessRoute($route, $username, $password, $parameters = [], $headers = [])
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form();
        $form = $this->generateLoginForm($form, $username, $password);
        $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));

        $client->followRedirect();
        $crawler = $client->request('GET', $route, $parameters, [], $headers);

        return [$client, $crawler];
    }
}
