<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/checkout', name: 'stripe_checkout', methods: ['POST'])]
    public function checkout(Request $request, StripeService $stripeService): JsonResponse
    {
        $lineItems = [];

        $cart = $this->getUser()->getCarts()->first(); // Supposons que getCarts() retourne une collection
        if (!$cart || count($cart->getProducts()) === 0) {
            return new JsonResponse(['error' => 'Votre panier est vide.'], 400);
        }

        $products = $cart->getProducts();
        foreach ($products as $product) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                    'unit_amount' => $product->getPrice() * 100, // Prix en centimes
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        try {
            $successUrl = $this->generateUrl('home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $cancelUrl = $this->generateUrl('cart_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $sessionId = $stripeService->createCheckoutSession($lineItems, $successUrl, $cancelUrl);

            return new JsonResponse(['id' => $sessionId]);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}