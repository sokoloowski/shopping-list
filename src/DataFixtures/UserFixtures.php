<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $userJanKowalski = new User();
        $hashedPassword = $this->passwordHasher->hashPassword($userJanKowalski, "password123");
        $userJanKowalski->setEmail("jan.kowalski@example.com")
            ->setUsername("jkowalski")
            ->setPassword($hashedPassword);
        $manager->persist($userJanKowalski);

        $userJohnDoe = new User();
        $hashedPassword = $this->passwordHasher->hashPassword($userJohnDoe, "password123");
        $userJohnDoe->setEmail("john.doe@example.com")
            ->setUsername("johndoe")
            ->setPassword($hashedPassword);
        $manager->persist($userJohnDoe);

        $manager->flush();

        $this->addReference("user-jan-kowalski", $userJanKowalski);
        $this->addReference("user-john-doe", $userJohnDoe);
    }
}
