<?php

namespace App\DataFixtures;

use App\Entity\Size;
use App\Entity\Product;
use App\Entity\ProductSize;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductSizeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $product = $this->getReference('product_' . $i, Product::class);

            foreach (SizeFixtures::SIZES as $sizeName) {
                $productSize = new ProductSize();
                $productSize->setProduct($product)
                            ->setSize($this->getReference('size_' . $sizeName, Size::class))
                            ->setStock(rand(5, 15));
                $manager->persist($productSize);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
            SizeFixtures::class,
        ];
    }
}