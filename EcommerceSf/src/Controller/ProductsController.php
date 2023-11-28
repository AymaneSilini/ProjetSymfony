<?php

namespace App\Controller;

use App\Entity\Products;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


#[Route('/produits', name: 'products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('products/index.html.twig');
    }

    #[Route('/{slug}', name: 'details')]
    public function details($slug, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Products::class);
        $product = $repository->findOneBy(['slug' => $slug]);
        return $this->render('products/details.html.twig', [
            'product' => $product
        ]);
    }
}
