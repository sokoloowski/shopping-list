<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\ShoppingList;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{

    public function testWhenProductIsCreated_ThenIdIsAssigned(): void
    {
        $product = new Product();
        $this->assertNotNull($product->getId());
    }

    public function testWhenProductIsCreated_ThenShoppingListIsAssigned(): void
    {
        $list = $this->createMock(ShoppingList::class);
        $product = new Product();
        $product->setShoppingList($list);
        $this->assertSame($list, $product->getShoppingList());
    }

    public function testWhenNameIsSet_ThenCorrectNameIsGet(): void
    {
        $product = new Product();
        $name = "[TEST] Some product name";
        $product->setName($name);
        $this->assertEquals($name, $product->getName());
    }

    public function testWhenQuantityIsSet_ThenCorrectQuantityIsGet(): void
    {
        $product = new Product();
        $quantity = 5;
        $product->setQuantity($quantity);
        $this->assertEquals($quantity, $product->getQuantity());
    }

    public function testWhenProductIsCreated_ThenRealisationIsFalse(): void
    {
        $product = new Product();
        $this->assertFalse($product->isRealised());
    }

    public function testWhenProductIsMarked_ThenRealisationIsTrue(): void
    {
        $product = new Product();
        $product->toggleRealisation();
        $this->assertTrue($product->isRealised());
    }

    public function testWhenProductIsMarked_ThenRealisationChanged(): void
    {
        $product = new Product();
        # testWhenProductIsCreated_ThenRealisationIsFalse passing
        # realisation is false
        $product->toggleRealisation();
        # testWhenProductIsMarked_ThenRealisationIsTrue passing
        # realisation is true
        $toggled = $product->isRealised();
        $product->toggleRealisation();
        # is realisation false again?
        $this->assertNotEquals($toggled, $product->isRealised());
    }
}
