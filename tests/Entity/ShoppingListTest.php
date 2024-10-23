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
        $this->assertSame($user, $list->getOwner());
    }

    public function testWhenProductIsAddedToShoppingList_ThenProductIsAddedToItems(): void
    {
        $list = new ShoppingList();
        $product = $this->createMock(Product::class);
        $list->addProduct($product);
        $this->assertContains($product, $list->getProducts());
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
        $this->assertNotNull($list->getName());
    }

    public function testWhenShoppingListIsCreated_ThenNameIsNotEmpty(): void
    {
        $list = new ShoppingList();
        $this->assertNotEmpty($list->getName());
    }

    public function testWhenNameIsSet_ThenCorrectNameIsGet(): void
    {
        $list = new ShoppingList();
        $name = "[TEST] Some shopping list name";
        $list->setName($name);
        $this->assertEquals($name, $list->getName());
    }

    public function testWhenPurchaseDateIsSet_ThenCorrectDateIsGet(): void
    {
        $list = new ShoppingList();
        $date = new \DateTimeImmutable();
        $list->setPurchaseDate($date);
        $this->assertEquals($date, $list->getPurchaseDate());
    }

    public function testWhenProductIsRemovedFromList_ThenProductRemovesParentList()
    {
        $list = new ShoppingList();
        $product = $this->createStub(Product::class);
        $product->method("getShoppingList")
            ->willReturn($list);
        $list->addProduct($product);
        $product->expects($this->once())
            ->method("setShoppingList")
            ->with(null);
        $list->removeProduct($product);
    }
}
