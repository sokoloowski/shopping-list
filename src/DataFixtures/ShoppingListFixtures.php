<?php

namespace App\DataFixtures;

use App\Entity\ShoppingList;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ShoppingListFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $userJanKowalski */
        $userJanKowalski = $this->getReference("user-jan-kowalski");

        /** @var User $userJohnDoe */
        $userJohnDoe = $this->getReference("user-john-doe");

        for ($i = 0; $i < 5; $i++) {
            $shoppingList = new ShoppingList();
            $shoppingList->setName("Shopping list " . $i)
                ->setPurchaseDate(new \DateTimeImmutable())
                ->setOwner($userJanKowalski);
            $manager->persist($shoppingList);

            $this->addReference("jan-kowalski-list-".$i, $shoppingList);
        }

        for ($i = 0; $i < 3; $i++) {
            $shoppingList = new ShoppingList();
            $shoppingList->setName("Shopping list " . $i)
                ->setPurchaseDate(new \DateTimeImmutable())
                ->setOwner($userJohnDoe);
            $manager->persist($shoppingList);

            $this->addReference("john-doe-list-".$i, $shoppingList);
        }

        $manager->flush();
    }
}
