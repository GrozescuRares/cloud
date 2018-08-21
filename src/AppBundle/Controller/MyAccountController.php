<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 21.08.2018
 * Time: 08:49
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class MyAccountController
 * @package AppBundle\Controller
 */
class MyAccountController extends Controller
{
    /**
     * @Route("/my-account", name="my-account")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function myAccountAction()
    {
        return $this->render('my_account/my-account.html.twig');
    }
}
