<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{
    //list all the products of a choosen category, with category slug
    #[Route('/{slug}', name: 'list')]
    public function list($slug, CategoriesRepository $categoriesRepository): Response
    {
        $category = $categoriesRepository->findOneBy(['slug' => $slug]);
        $products = $category->getProducts();
        return $this->render('categories/list.html.twig', compact('category', 'products'));
    }
}
