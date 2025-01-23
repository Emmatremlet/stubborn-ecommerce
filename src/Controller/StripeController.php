<?php

namespace App\Controller;

use App\Service\StripeService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    #[Route('/checkout', name: 'stripe_checkout', methods: ['POST'])]
    public function checkout(Request $request, StripeService $stripeService): JsonResponse
    {
        $lineItems = [];

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié.'], 401);
        }

        $cart = $user->getOrCreateCart();        
        if (!$cart || count($cart->getProducts()) === 0) {
            return $this->json(['error' => 'Votre panier est vide.'], 400);
        }

        $products = $cart->getProducts();
        
        foreach ($products as $product) {
            if ($product->getPrice() <= 0) {
                return new JsonResponse(['error' => 'Le produit "' . $product->getName() . '" a un prix invalide.'], 400);
            } else {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $product->getName(),
                        ],
                        'unit_amount' => $product->getPrice() * 100,
                    ],
                    'quantity' => $product->getQuantity(),
                ];
            }
        }

        try {
            $successUrl = $this->generateUrl('home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $cancelUrl = $this->generateUrl('cart_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $sessionId = $stripeService->createCheckoutSession($lineItems, $successUrl, $cancelUrl);

            return new JsonResponse(['id' => $sessionId]);
        } catch (\RuntimeException $e) {
            $this->get('logger')->error('Erreur Stripe : ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}