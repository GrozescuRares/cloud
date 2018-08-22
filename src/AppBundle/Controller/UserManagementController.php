<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 09:34
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserManagementController
 * @package AppBundle\Controller
 */
class UserManagementController extends Controller
{

    /**
     * @Route("/user-management/add-user", name="add-user")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addUserAction()
    {
        return $this->render('user_management/add-user.html.twig');
    }
}
