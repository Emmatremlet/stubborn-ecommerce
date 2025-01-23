<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\ProductSize;
use App\Entity\Size;

class CartControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $user;
    private $cart;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $this->prepareTestData();
    }

    private function prepareTestData(): void
    {
        $user = new User();
        $user->setName('Test User');
        $user->setEmail('testuser@example.com');
        $user->setPassword('password123');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        $size = new Size();
        $size->setName('M');
        $this->entityManager->persist($size);
        $this->entityManager->flush();      

        $product = new Product();
        $product->setName('Test Product');
        $product->setPrice(50);
        $product->setHighlighted(true);
        $this->entityManager->persist($product);

        $productSize = new ProductSize();
        $productSize->setSize($size);
        $productSize->setProduct($product);
        $productSize->setStock(10);
        $this->entityManager->persist($productSize);
        $this->entityManager->flush();  

        $selectedSize = new ProductSize();
        $selectedSize->setSize($size);
        $selectedSize->setProduct($product);
        $selectedSize->setStock(10);
        $this->entityManager->persist($selectedSize);

        $cart = new Cart();
        $cart->setUser($user);
        $cart->addProduct($product);
        $product->setQuantity(1);
        $product->setSelectedSize($productSize);
        $cart->setTotalPrice(50.0);
        
        $this->entityManager->persist($cart);

        $this->entityManager->flush();

        $this->user = $user;
        $this->cart = $cart;
        $this->product = $product;
    }

    public function testAddToCart(): void
    {
        $this->client->request('POST', '/cart/add/' . $this->product->getId(), [
            'size' => 1,
            'quantity' => 2,
        ]);

        $this->assertResponseRedirects('/cart');
        $this->client->followRedirect();
        $this->assertSelectorExists('.cart-items', 'Le produit est bien ajouté au panier.');
    }

    public function testUpdateCart(): void
    {
        $data = [
            'sizes' => [
                $this->product->getId() => 1,
            ],
            'quantities' => [
                $this->product->getId() => 3,
            ],
        ];

        $this->client->request('POST', '/cart/update', $data);
        $this->assertResponseRedirects('/cart');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testRemoveFromCart(): void
    {
        $this->client->request('POST', '/cart/remove/' . $this->product->getId());

        $this->assertResponseRedirects('/cart');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }
}