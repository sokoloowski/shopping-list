<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\ShoppingList;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
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

    public function testWhenRealisationIsFalse_ThenToggleSetTrue(): void
    {
        $product = new Product();
        $product->toggleRealisation();
        $this->assertTrue($product->isRealised());
    }

    public function testWhenRealisationIsSet_ThenCorrectRealisationIsGet(): void
    {
        $product = new Product();
        $product->setRealisation(true);
        $this->assertTrue($product->isRealised());
    }

    public function testWhenRealisationIsTrue_ThenToggleSetFalse(): void
    {
        $product = new Product();
        $product->setRealisation(true);
        $product->toggleRealisation();
        $this->assertFalse($product->isRealised());
    }
}
