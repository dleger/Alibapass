<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class LogoutController extends Controller
{
    /**
     * @Route("/logout/"), name="logout", options={"expose"=true})
     */
    public function logoutAction(Request $request) {
        $session = new Session();

        $session->clear();

        return $this->redirectToRoute('login');
    }
}