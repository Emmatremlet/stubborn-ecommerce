<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'products')]
    public function list(ProductRepository $productRepository, Request $request): Response
    {
        $priceRange = $request->query->get('price_range');
        $products = $productRepository->findAll();

        if ($priceRange) {
            [$min, $max] = explode('-', $priceRange);
            $products = $productRepository->findByPriceRange($min, $max);
        }

        return $this->render('product/list.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'product')]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/dashboard', name: 'product_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');

            return $this->redirectToRoute('/dashboard');
        }

        return $this->render('administrator/dashboard.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

?>