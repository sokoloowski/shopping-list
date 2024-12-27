<?php

namespace App\Tests\Integration\Entity;

use App\Entity\Product;
use App\Entity\ShoppingList;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ShoppingListTest extends TestCase
{
    public function testWhenShoppingListIsCreated_ThenOwnerIsAssigned(): void
    {
        $user = $this->createMock(User::class);
        $list = new ShoppingList();
        $list->setOwner($user);
        self::assertSame($user, $list->getOwner());
    }

    public function testWhenProductIsAddedToShoppingList_ThenProductIsAddedToItems(): void
    {
        $list = new ShoppingList();
        $product = $this->createMock(Product::class);
        $list->addProduct($product);
        self::assertContains($product, $list->getProducts());
    }

    public function testWhenProductIsAddedToShoppingList_ThenProductShoppingListWillBeSet(): void
    {
        $list = new ShoppingList();
        $product = $this->createMock(Product::class);
        $product->expects($this->once())
            ->method("setShoppingList")
            ->with($list);
        $list->addProduct($product);
    }

    public function testWhenProductIsRemovedFromList_ThenProductRemovesParentList(): void
    {
        $list = new ShoppingList();
        $product = $this->createMock(Product::class);
        $product->method("getShoppingList")
            ->willReturn($list);
        $list->addProduct($product);
        $product->expects($this->once())
            ->method("setShoppingList")
            ->with(null);
        $list->removeProduct($product);
    }
}
