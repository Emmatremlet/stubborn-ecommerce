<?php

namespace App\DataFixtures;

use App\Entity\Size;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SizeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

        foreach ($sizes as $sizeName) {
            $size = new Size();
            $size->setName($sizeName);
            $manager->persist($size);
        }

        $manager->flush();
    }
}
?>