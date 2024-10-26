<?php

namespace App\Tests\Entity;

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

    public function testWhenShoppingListIsCreated_ThenNameIsNotNull(): void
    {
        $list = new ShoppingList();
        self::assertNotNull($list->getName());
    }

    public function testWhenShoppingListIsCreated_ThenNameIsNotEmpty(): void
    {
        $list = new ShoppingList();
        self::assertNotEmpty($list->getName());
    }

    public function testWhenNameIsSet_ThenCorrectNameIsGet(): void
    {
        $list = new ShoppingList();
        $name = "[TEST] Some shopping list name";
        $list->setName($name);
        self::assertEquals($name, $list->getName());
    }

    public function testWhenPurchaseDateIsSet_ThenCorrectDateIsGet(): void
    {
        $list = new ShoppingList();
        $date = new \DateTimeImmutable();
        $list->setPurchaseDate($date);
        self::assertEquals($date, $list->getPurchaseDate());
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
