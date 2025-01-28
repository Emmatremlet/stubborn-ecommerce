<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Size;
use App\Entity\ProductSize;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $productData = [
            ['Blackbelt', 29.90, true, '1.jpeg'],
            ['BlueBelt', 29.90, false, '2.jpeg'],
            ['Street', 34.50, false, '3.jpeg'],
            ['Pokeball', 45.00, true, '4.jpeg'],
            ['PinkLady', 29.90, false, '5.jpeg'],
            ['Snow', 32.00, false, '6.jpeg'],
            ['Greyback', 28.50, false, '7.jpeg'],
            ['BlueCloud', 45.00, false, '8.jpeg'],
            ['BornInUsa', 59.90, true, '9.jpeg'],
            ['GreenSchool', 42.20, false, '10.jpeg'],
        ];

        foreach ($productData as $index => [$name, $price, $highlighted, $image]) {
            $product = new Product();
            $product->setName($name)
                    ->setPrice($price)
                    ->setHighlighted($highlighted)
                    ->setImage($image);

            foreach (SizeFixtures::SIZES as $sizeName) {
                $productSize = new ProductSize();
                $productSize->setProduct($product)
                            ->setSize($this->getReference('size_' . $sizeName, Size::class))
                            ->setStock(rand(5, 15));
                $manager->persist($productSize);
            }

            $manager->persist($product);

            $this->addReference('product_' . $index, $product);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SizeFixtures::class,
        ];
    }
}