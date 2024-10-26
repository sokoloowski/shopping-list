<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\ProductUnitEnum;
use App\Entity\ShoppingList;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testWhenProductIsCreated_ThenShoppingListIsAssigned(): void
    {
        $list = $this->createMock(ShoppingList::class);
        $product = new Product();
        $product->setShoppingList($list);
        self::assertSame($list, $product->getShoppingList());
    }

    public function testWhenNameIsSet_ThenCorrectNameIsGet(): void
    {
        $product = new Product();
        $name = "[TEST] Some product name";
        $product->setName($name);
        self::assertEquals($name, $product->getName());
    }

    public function testWhenQuantityIsSet_ThenCorrectQuantityIsGet(): void
    {
        $product = new Product();
        $quantity = 5;
        $product->setQuantity($quantity);
        self::assertEquals($quantity, $product->getQuantity());
    }

    public function testWhenProductIsCreated_ThenRealisationIsFalse(): void
    {
        $product = new Product();
        self::assertFalse($product->isRealised());
    }

    public function testWhenRealisationIsFalse_ThenToggleSetTrue(): void
    {
        $product = new Product();
        $product->toggleRealisation();
        self::assertTrue($product->isRealised());
    }

    public function testWhenRealisationIsSet_ThenCorrectRealisationIsGet(): void
    {
        $product = new Product();
        $product->setRealisation(true);
        self::assertTrue($product->isRealised());
    }

    public function testWhenRealisationIsTrue_ThenToggleSetFalse(): void
    {
        $product = new Product();
        $product->setRealisation(true);
        $product->toggleRealisation();
        self::assertFalse($product->isRealised());
    }

    public function testWhenUnitIsSet_ThenCorrectUnitIsGet(): void
    {
        $product = new Product();
        $unit = ProductUnitEnum::ML;
        $product->setUnit($unit);
        self::assertEquals($unit, $product->getUnit());
    }
}
