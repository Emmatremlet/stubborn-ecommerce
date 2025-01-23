<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\ProductSize;
use App\Entity\Size;
use App\Entity\Cart;


class StripeControllerTest extends WebTestCase
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

        $productSize = new ProductSize();
        $productSize->setSize($size);
        $productSize->setStock(10);
        $this->entityManager->persist($productSize);
        $this->entityManager->flush();  

        $selectedSize = new ProductSize();
        $selectedSize->setSize($size);
        $selectedSize->setStock(10);
        $this->entityManager->persist($selectedSize);
        $this->entityManager->flush();  

        $product = new Product();
        $product->setName('Test Product');
        $product->setPrice(50);
        $product->setHighlighted(true);
        $product->setSelectedSize($selectedSize);
        $productSize->setProduct($product);
        $product->addProductSize($productSize);
        $this->entityManager->persist($product);
        $this->entityManager->flush();  

        $cart = new Cart();
        $cart->setUser($user);
        $user->addCart($cart);
        $cart->addProduct($product);
        $product->setQuantity(1);
        $product->setSelectedSize($productSize);
        $cart->setTotalPrice(50.0);

        $this->entityManager->persist($cart);
        $this->entityManager->flush();   
        
        // dump($cart);
        // die();

        $this->user = $user;
        $this->cart = $cart;
        $this->product = $product;
    }

    public function testStripeCheckout(): void
    {
        $this->assertNotNull($this->cart);
        $this->assertCount(1, $this->cart->getProducts());
        $this->assertNotEmpty($this->cart->getProducts(), 'Le panier ne doit pas être vide.');
        $this->assertEquals(50.0, $this->cart->getTotalPrice());
        $this->assertNotNull($this->product->getSelectedSize(), 'La taille sélectionnée doit être définie.');
        $this->assertNotNull($this->product->getSelectedSize()->getSize(), 'La taille associée doit être définie.');
        $this->assertNotNull($this->product->getSelectedSize()->getSize()->getId(), 'L\'ID de la taille associée doit être valide.');

        $this->client->request('POST', '/checkout', [
            'orderId' => $this->cart->getId(),
        ]);

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testCheckoutWithEmptyCart(): void
    {
        $this->assertNotNull($this->cart);
        $this->assertNotEmpty($this->cart->getProducts(), 'Le panier doit contenir des produits.');
        $this->assertEquals(50.0, $this->cart->getTotalPrice(), 'Le total du panier doit être 50.0.');
        $this->assertEquals($this->user, $this->cart->getUser(), 'Le panier doit être associé à l\'utilisateur connecté.');
        $this->assertNotNull($this->product->getSelectedSize(), 'La taille sélectionnée doit être définie.');
        $this->assertNotNull($this->product->getSelectedSize()->getSize(), 'La taille associée doit être définie.');
        $this->assertNotNull($this->product->getSelectedSize()->getSize()->getId(), 'L\'ID de la taille associée doit être valide.');
        $this->client->request('POST', '/checkout');
        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());
    }

    public function testCheckoutWithValidCart(): void
    {
        $this->assertNotNull($this->cart);
        $this->assertNotEmpty($this->cart->getProducts(), 'Le panier doit contenir des produits.');
        $this->assertEquals(50.0, $this->cart->getTotalPrice(), 'Le total du panier doit être 50.0.');
        $this->assertEquals($this->user, $this->cart->getUser(), 'Le panier doit être associé à l\'utilisateur connecté.');        $this->assertNotNull($this->product->getSelectedSize(), 'La taille sélectionnée doit être définie.');
        $this->assertNotNull($this->product->getSelectedSize()->getSize(), 'La taille associée doit être définie.');
        $this->assertNotNull($this->product->getSelectedSize()->getSize()->getId(), 'L\'ID de la taille associée doit être valide.');
        $this->client->request('POST', '/checkout', [
            'orderId' => $this->cart->getId(),
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());
        $this->assertArrayHasKey('id', json_decode($response->getContent(), true));
    }
}