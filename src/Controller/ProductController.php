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
    public function list(ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $priceRange = $request->query->get('price_range');
        $products = $productRepository->findAll();

        $productRepository = $entityManager->getRepository(Product::class);
        $queryBuilder = $productRepository->createQueryBuilder('p');

        if ($priceRange) {
            [$minPrice, $maxPrice] = explode('-', $priceRange);
            $queryBuilder->where('p.price >= :minPrice')
                ->andWhere('p.price <= :maxPrice')
                ->setParameter('minPrice', $minPrice)
                ->setParameter('maxPrice', $maxPrice);
        }

        $products = $queryBuilder->getQuery()->getResult();

        return $this->render('product/list.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'product')]
    public function show(Product $product): Response
    {
        $availableSizes = [];
        foreach ($product->getProductSizes() as $productSize) {
            if ($productSize->getStock() > 0) {
                $availableSizes[] = $productSize->getSize();
            }
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'availableSizes' => $availableSizes,
        ]);
    }

    #[Route('/dashboard', name: 'admin')]
    public function new(ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $products = $productRepository->findAll();
        $product = new Product();
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');

            return $this->redirectToRoute('product_new');
        }

        return $this->render('administrator/dashboard.html.twig', [
            'form' => $form->createView(),
            'products' => $products
        ]);
    }
        
    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');

            return $this->redirectToRoute('admin');
        }

        return $this->render('administrator/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/product/delete/{id}', name: 'product_delete')]
    public function delete(Product $product, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé avec succès !');
        return $this->redirectToRoute('products');
    }
}

?>