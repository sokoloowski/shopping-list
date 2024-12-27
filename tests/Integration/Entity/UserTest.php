<?php

namespace App\Tests\Integration\Entity;

use App\Entity\ShoppingList;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    public function testWhenUserCreatesNewShoppingList_ThenShoppingListIsAddedToUser(): void
    {
        $user = new User();
        $list = $this->createMock(ShoppingList::class);
        $user->addShoppingList($list);
        self::assertContains($list, $user->getShoppingLists());
    }

    public function testWhenUserCreatesNewShoppingList_ThenShoppingListOwnerWillBeSet(): void
    {
        $user = new User();
        $list = $this->createMock(ShoppingList::class);
        $list->expects($this->once())
            ->method("setOwner")
            ->with($user);
        $user->addShoppingList($list);
    }

    public function testWhenShoppingListIsRemovedFromUser_ThenShoppingListRemovesParentList(): void
    {
        $user = new User();
        $list = $this->createMock(ShoppingList::class);
        $list->method("getOwner")
            ->willReturn($user);
        $user->addShoppingList($list);
        $list->expects($this->once())
            ->method("setOwner")
            ->with(null);
        $user->removeShoppingList($list);
    }
}
