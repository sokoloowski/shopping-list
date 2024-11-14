<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductUnitEnum;
use App\Entity\ShoppingList;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            ShoppingListFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var ShoppingList $jkList */
        $jkList = $this->getReference("jan-kowalski-list-0");

        /** @var ShoppingList $jdList */
        $jdList = $this->getReference("john-doe-list-0");

        for ($i = 0; $i < 15; $i++) {
            $product = new Product();
            $product->setName("Product " . $i)
                ->setQuantity($i * 100)
                ->setUnit(ProductUnitEnum::G)
                ->setRealisation($i % 3 === 0)
                ->setShoppingList($jkList);
            $manager->persist($product);
        }

        for ($i = 0; $i < 30; $i++) {
            $product = new Product();
            $product->setName("Product " . $i)
                ->setQuantity($i * 2)
                ->setUnit(ProductUnitEnum::PKG)
                ->setRealisation($i % 3 === 0)
                ->setShoppingList($jdList);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
