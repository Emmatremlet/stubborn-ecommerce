<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $userData = [
            ['client@example.com', 'password', 'ROLE_USER'],
            ['admin@example.com', 'admin', 'ROLE_ADMIN'],
        ];

        foreach ($userData as [$email, $password, $role]) {
            $user = new User();
            $user->setEmail($email)
                 ->setName(explode('@', $email)[0])
                 ->setRoles([$role])
                 ->setPassword($this->passwordHasher->hashPassword($user, $password))
                 ->setDeliveryAddress('123 Main Street');
            $manager->persist($user);

            $this->addReference('user_' . $role, $user);
        }

        $manager->flush();
    }
}