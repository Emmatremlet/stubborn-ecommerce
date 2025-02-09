<?php
namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\ProductSize;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CartController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/cart', name: 'cart_index')]
    public function index(): Response
    {
        $cart = $this->getUser()->getOrCreateCart();

        if (!$cart) {
            $this->addFlash('danger', 'Vous n\'avez pas encore de panier.');
            return $this->redirectToRoute('products');
        }

        $products = $cart->getProducts();

        $selectedSizeIds = [];
        foreach ($products as $product) {
            $selectedSize = $product->getSelectedSize();

            $selectedSizeIds[$product->getId()] = $selectedSize ? $selectedSize->getId() : null;

            // Vérifier et mettre à jour la taille sélectionnée
            foreach ($product->getProductSizes() as $productSize) {
                if (
                    $productSize->getSize() &&
                    $selectedSize &&
                    $productSize->getSize()->getId() == $selectedSize->getId()
                ) {
                    $selectedSizeIds[$product->getId()] = $selectedSize->getId();
                    break;
                }
            }
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'products' => $products,
            'selected_size_id' => $selectedSizeIds,
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'] ?? null,
        ]);
    }

    #[Route('/cart/add/{productId}', name: 'cart_add', methods: ['POST'])]
    public function addToCart(int $productId, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour ajouter des produits au panier.');
            return $this->redirectToRoute('login');
        }

        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            $this->addFlash('danger', 'Produit non trouvé.');
            return $this->redirectToRoute('products');
        }
        
        $sizeId = $request->request->get('size');
        if (!$sizeId || !is_numeric($sizeId)) {
            throw new \InvalidArgumentException('Veuillez sélectionner une taille valide.');
        }

        $productSize = $this->entityManager->getRepository(ProductSize::class)->find($sizeId);
        if (!$productSize) {
            throw new \InvalidArgumentException('La taille associée est invalide (controller).');
        }
        
        $cart = $this->getUser()->getOrCreateCart();
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($this->getUser());
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
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
            $existingProduct->setSelectedSize($productSize);
        } else {
            $cart->addProduct($product);
            $product->setQuantity(1);
            $product->setSelectedSize($productSize);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Produit ajouté au panier avec la taille sélectionnée !');
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/update', name: 'cart_update', methods: ['POST'])]
    public function updateCart(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $cart = $this->getUser()->getOrCreateCart();

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($this->getUser());
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        $data = $request->request->all();
        $sizes = $data['sizes'] ?? [];
        $quantities = $data['quantities'] ?? [];

        if (!is_array($sizes) || !is_array($quantities)) {
            throw new BadRequestHttpException('Invalid input data.');
        }

        foreach ($cart->getProducts() as $product) {
            $productId = $product->getId();

            if (isset($quantities[$productId]) && is_numeric($quantities[$productId])) {
                $quantity = (int) $quantities[$productId];
                if ($quantity > 0) {
                    $product->setQuantity($quantity);
                }
            }

            if (isset($sizes[$productId]) && is_numeric($sizes[$productId])) {
                $sizeId = (int) $sizes[$productId];
                $productSize = $entityManager->getRepository(ProductSize::class)->find($sizeId);

                if (!$productSize) {
                    $this->addFlash('danger', 'La taille sélectionnée est invalide pour le produit ' . $product->getName() . '.');
                    continue;
                }

                $product->setSelectedSize($productSize);
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Le panier a été mis à jour.');
        return $this->redirectToRoute('cart_index');
    }
    
    #[Route('/cart/remove/{productId}', name: 'cart_remove')]
    public function removeFromCart(int $productId, EntityManagerInterface $entityManager): RedirectResponse
    {
        $cart = $this->getUser()->getCarts()->first();

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($this->getUser());
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            $this->addFlash('danger', 'Produit non trouvé.');
            return $this->redirectToRoute('cart_index');
        }

        $cartItem = null;
        foreach ($cart->getProducts() as $item) {
            if ($item === $product) {
                $cartItem = $item;
                break;
            }
        }

        if ($cartItem) {
            $cart->removeProduct($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit supprimé du panier !');
        } else {
            $this->addFlash('danger', 'Produit non trouvé dans le panier.');
        }

        return $this->redirectToRoute('cart_index');
    }
}
?>