<?php

namespace App\Tests\Integration\Entity;

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

    public function testWhenUnitIsSet_ThenCorrectUnitIsGet(): void
    {
        $product = new Product();
        $unit = ProductUnitEnum::ML;
        $product->setUnit($unit);
        self::assertEquals($unit, $product->getUnit());
    }
}
