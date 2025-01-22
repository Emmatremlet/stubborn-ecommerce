<?php
namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart_index')]   
    public function index(): Response
    {
        $cart = $this->getUser()->getCarts()->first();

        if (!$cart) {
            $this->addFlash('error', 'Vous n\'avez pas encore de panier.');
            return $this->redirectToRoute('products');
        }

        $products = $cart->getProducts();

        $selectedSizeIds = [];
        foreach ($products as $product) {
            $selectedSizeIds[$product->getId()] = null;
            foreach ($product->getProductSizes() as $productSize) {
                if ($productSize->getSize() && $productSize->getSize()->getId() == $product->getSelectedSize()->getId()) {
                    $selectedSizeIds[$product->getId()] = $product->getSelectedSize()->getId();
                    break;
                }
            }
        }
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'products' => $products,
            'selected_size_id' => $selectedSizeIds,
        ]);
    }


    #[Route('/cart/add/{productId}', name: 'cart_add')]
    public function addToCart(int $productId, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter des produits au panier.');

            return $this->redirectToRoute('login');
        }
        
        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            $this->addFlash('error', 'Produit non trouvé.');
            return $this->redirectToRoute('products');
        }

        $cart = $this->getUser()->getCarts()->first(); 

        if (!$cart) {
            $cart = new Cart();
            $this->getUser()->addCart($cart);
            $entityManager->persist($cart);
        }

        $existingProduct = null;
        foreach ($cart->getProducts() as $productInCart) {
            if ($productInCart === $product) {
                $existingProduct = $productInCart;
                break;
            }
        }

        if ($existingProduct) {
            $existingProduct->setQuantity($existingProduct->getQuantity() + 1);
        } else {
            $cart->addProduct($product);
            $product->setQuantity(1);
        }
                
        $entityManager->flush();

        $this->addFlash('success', 'Produit ajouté au panier !');

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/update', name: 'cart_update')]
    public function updateCart(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $cart = $this->getUser()->getCarts()->first();

        if (!$cart) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        $quantities = $request->request->get('quantities');
        dump($quantities);
        dump($sizes);   
        $sizes = $request->request->get('sizes');

        foreach ($cart->getProducts() as $product) {
            $productId = $product->getId();

            if (isset($quantities[$productId])) {
                $quantity = (int) $quantities[$productId];
                
                if ($quantity > 0) {
                    $product->setQuantity($quantity);

                    if (isset($sizes[$productId])) {
                        $sizeId = $sizes[$productId];
                        $productSize = $entityManager->getRepository(ProductSize::class)->find($sizeId);
                        if ($productSize) {
                            $productSize->setStock($productSize->getStock() - $quantity);
                        }
                    }
                }
            }
        }

        $entityManager->flush();
        $this->addFlash('success', 'Le panier a été mis à jour.');

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/remove/{productId}', name: 'cart_remove')]
    public function removeFromCart(int $productId, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Récupérer le panier de l'utilisateur
        $cart = $this->getUser()->getCarts()->first();

        if (!$cart) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        // Récupérer le produit à supprimer
        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            $this->addFlash('error', 'Produit non trouvé.');
            return $this->redirectToRoute('cart_index');
        }

        // Rechercher l'association du produit dans le panier
        $cartItem = null;
        foreach ($cart->getProducts() as $item) {
            if ($item === $product) {
                $cartItem = $item;
                break;
            }
        }

        if ($cartItem) {
            // Retirer le produit du panier (enlever la relation)
            $cart->removeProduct($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit supprimé du panier !');
        } else {
            $this->addFlash('error', 'Produit non trouvé dans le panier.');
        }

        return $this->redirectToRoute('cart_index');
    }
}
?>