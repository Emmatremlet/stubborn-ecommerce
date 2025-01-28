<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CartFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $cart = new Cart();
        $cart->setUser($this->getReference('user_ROLE_USER', User::class));
        $cart->addProduct($this->getReference('product_0', Product::class));
        $cart->setTotalPrice($this->getReference('product_0', Product::class)->getPrice());

        $manager->persist($cart);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProductFixtures::class,
        ];
    }
}