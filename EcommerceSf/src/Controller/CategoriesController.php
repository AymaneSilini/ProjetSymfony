<?php

namespace App\Controller;

use App\Entity\Categories;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/{slug}', name: 'list')]
    public function list($slug, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Categories::class);
        $category = $repository->findOneBy(['slug' => $slug]);
        return $this->render('categories/list.html.twig', compact('category'));
    }
}
