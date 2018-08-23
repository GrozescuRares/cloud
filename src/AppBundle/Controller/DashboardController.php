<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 14.08.2018
 * Time: 13:47
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DashboardController
 * @package AppBundle\Controller
 */
class DashboardController extends Controller
{

    /**
     * @Route("/", name="dashboard")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dashboardAction()
    {
        return $this->render('dashboard/dashboard.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
