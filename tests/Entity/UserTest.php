<?php

namespace App\Tests\Entity;

use App\Entity\ShoppingList;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserTest extends TestCase
{
    public function testWhenUserVerifiedAtIsNull_ThenUserVerifiedIsFalse(): void
    {
        $user = new User();
        $this->assertFalse($user->isVerified());
    }

    public function testWhenUserVerifiedAtIsNull_ThenUserVerificationDateIsNull(): void
    {
        $user = new User();
        $this->assertNull($user->getVerifiedAt());
    }

    public function testWhenUserIsRegistered_ThenRegisteredAtIsAfterCreation(): void
    {
        $dateBeforeRegister = new \DateTimeImmutable();
        $user = new User();
        $this->assertGreaterThan($dateBeforeRegister, $user->getRegisteredAt());;
    }

    public function testWhenUserIsRegistered_ThenRegisteredAtIsBeforeNow(): void
    {
        $user = new User();
        $this->assertLessThan(new \DateTimeImmutable(), $user->getRegisteredAt());
    }

    public function testWhenUserIsRegistered_ThenVerificationCodeIsNotNull(): void
    {
        $user = new User();
        $this->assertNotNull($user->getVerificationCode());
    }

    public function testWhenUserIsRegistered_ThenVerificationCodeIsNotEmpty(): void
    {
        $user = new User();
        $this->assertNotEmpty($user->getVerificationCode());
    }

    public function testWhenUserIsRegistered_ThenVerificationCodeHasCertainLength(): void
    {
        $user = new User();
        $this->assertEquals(12, strlen($user->getVerificationCode()));
    }

    public function testWhenWrongVerificationCodeUsed_ThenHttpExceptionIsThrown(): void
    {
        $user = new User();
        $this->expectException(HttpException::class);
        $user->verify($user->getVerificationCode() . "_incorrect");
    }

    public function testWhenCorrectVerificationCodeUsed_ThenVerificationDateIsSet(): void
    {
        $user = new User();
        $user->verify($user->getVerificationCode());
        $this->assertNotNull($user->getVerifiedAt());
    }

    public function testWhenCorrectVerificationCodeUsed_ThenVerificationDateIsAfterVerification(): void
    {
        $user = new User();
        $dateBeforeVerification = new \DateTimeImmutable();
        $user->verify($user->getVerificationCode());
        $this->assertGreaterThan($dateBeforeVerification, $user->getVerifiedAt());
    }

    public function testWhenCorrectVerificationCodeUsed_ThenVerificationDateIsBeforeNow(): void
    {
        $user = new User();
        $user->verify($user->getVerificationCode());
        $this->assertLessThan(new \DateTimeImmutable(), $user->getVerifiedAt());
    }

    public function testWhenUserIsVerified_ThenVerificationIsAfterRegistration(): void
    {
        $user = new User();
        $user->verify($user->getVerificationCode());
        $this->assertGreaterThan($user->getRegisteredAt(), $user->getVerifiedAt());
    }

    public function testWhenUserCreatesNewShoppingList_ThenShoppingListIsAddedToUser(): void
    {
        $user = new User();
        $list = $this->createMock(ShoppingList::class);
        $user->addShoppingList($list);
        $this->assertContains($list, $user->getShoppingLists());
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

    public function testWhenUserLogIn_ThenLastLogInDateIsUpdated(): void
    {
        $user = new User();
        $user->setLoggedIn();
        $lastLogin = $user->getLastLogInDate();
        $user->setLoggedIn();
        $this->assertGreaterThan($lastLogin, $user->getLastLogInDate());
    }

    public function testWhenUserRegister_ThenLastLogInDateIsNull(): void
    {
        $user = new User();
        $this->assertNull($user->getLastLogInDate());
    }

    public function testWhenUserLogIn_ThenLastLogInDateIsNotNull(): void
    {
        $user = new User();
        $user->setLoggedIn();
        $this->assertNotNull($user->getLastLogInDate());
    }

    public function testWhenProductIsRemovedFromList_ThenProductRemovesParentList()
    {
        $user = new User();
        $list = $this->createStub(ShoppingList::class);
        $list->method("getOwner")
            ->willReturn($user);
        $user->addShoppingList($list);
        $list->expects($this->once())
            ->method("setOwner")
            ->with(null);
        $user->removeShoppingList($list);
    }
}
