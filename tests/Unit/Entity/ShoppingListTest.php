<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ShoppingList;
use PHPUnit\Framework\TestCase;

class ShoppingListTest extends TestCase
{
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
}
