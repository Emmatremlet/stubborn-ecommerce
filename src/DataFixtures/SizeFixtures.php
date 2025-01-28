<?php

namespace App\DataFixtures;

use App\Entity\Size;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SizeFixtures extends Fixture
{
    public const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    public function load(ObjectManager $manager): void
    {
        foreach (self::SIZES as $sizeName) {
            $size = new Size();
            $size->setName($sizeName);
            $manager->persist($size);

            $this->addReference('size_' . $sizeName, $size);
        }

        $manager->flush();
    }
}