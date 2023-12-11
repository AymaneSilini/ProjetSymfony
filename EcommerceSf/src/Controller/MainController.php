<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    //how to access the route
    #[Route('/', name: 'main')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        //what to render for this route
        return $this->render('main/index.html.twig', [
            'categories' => $categoriesRepository->findBy(
                [],
                ['categoryOrder' => 'asc']
            )
        ]);
    }
}
