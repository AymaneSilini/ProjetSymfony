<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    //how to access the route
    #[Route('/', name: 'main')]
    public function index(): Response
    {
        //what to render for this route
        return $this->render('main/index.html.twig');
    }
}
