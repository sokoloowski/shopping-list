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
        self::assertFalse($user->isVerified());
    }

    public function testWhenUserVerifiedAtIsNull_ThenUserVerificationDateIsNull(): void
    {
        $user = new User();
        self::assertNull($user->getVerifiedAt());
    }

    public function testWhenUserIsRegistered_ThenRegisteredAtIsAfterCreation(): void
    {
        $dateBeforeRegister = new \DateTimeImmutable();
        $user = new User();
        self::assertGreaterThan($dateBeforeRegister, $user->getRegisteredAt());
    }

    public function testWhenUserIsRegistered_ThenRegisteredAtIsBeforeNow(): void
    {
        $user = new User();
        self::assertLessThanOrEqual(new \DateTimeImmutable(), $user->getRegisteredAt());
    }

    public function testWhenUserIsRegistered_ThenVerificationCodeIsNotEmpty(): void
    {
        $user = new User();
        self::assertNotEmpty($user->getVerificationCode());
    }

    public function testWhenUserIsRegistered_ThenVerificationCodeHasCertainLength(): void
    {
        $user = new User();
        self::assertEquals(12, strlen($user->getVerificationCode()));
    }

    public function testWhenWrongVerificationCodeUsed_ThenHttpExceptionIsThrown(): void
    {
        $user = new User();
        self::expectException(HttpException::class);
        $user->verify($user->getVerificationCode() . "_incorrect");
    }

    public function testWhenCorrectVerificationCodeUsed_ThenVerificationDateIsSet(): void
    {
        $user = new User();
        $user->verify($user->getVerificationCode());
        self::assertNotNull($user->getVerifiedAt());
    }

    public function testWhenCorrectVerificationCodeUsed_ThenVerificationDateIsAfterVerification(): void
    {
        $user = new User();
        $dateBeforeVerification = new \DateTimeImmutable();
        $user->verify($user->getVerificationCode());
        self::assertGreaterThan($dateBeforeVerification, $user->getVerifiedAt());
    }

    public function testWhenCorrectVerificationCodeUsed_ThenVerificationDateIsBeforeNow(): void
    {
        $user = new User();
        $user->verify($user->getVerificationCode());
        self::assertLessThanOrEqual(new \DateTimeImmutable(), $user->getVerifiedAt());
    }

    public function testWhenUserIsVerified_ThenVerificationIsAfterRegistration(): void
    {
        $user = new User();
        $user->verify($user->getVerificationCode());
        self::assertGreaterThan($user->getRegisteredAt(), $user->getVerifiedAt());
    }

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

    public function testWhenUserLogIn_ThenLastLogInDateIsUpdated(): void
    {
        $user = new User();
        $user->setLoggedIn();
        $lastLogin = $user->getLastLogInDate();
        $user->setLoggedIn();
        self::assertGreaterThan($lastLogin, $user->getLastLogInDate());
    }

    public function testWhenUserRegister_ThenLastLogInDateIsNull(): void
    {
        $user = new User();
        self::assertNull($user->getLastLogInDate());
    }

    public function testWhenUserLogIn_ThenLastLogInDateIsNotNull(): void
    {
        $user = new User();
        $user->setLoggedIn();
        self::assertNotNull($user->getLastLogInDate());
    }

    public function testWhenProductIsRemovedFromList_ThenProductRemovesParentList(): void
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

    public function testWhenUsernameIsSet_ThenCorrectUsernameIsGet(): void
    {
        $user = new User();
        $name = "testusername";
        $user->setUsername($name);
        self::assertEquals($name, $user->getUsername());
    }

    public function testWhenEmailIsSet_ThenCorrectEmailIsGet(): void
    {
        $user = new User();
        $email = "test.me@example.com";
        $user->setEmail($email);
        self::assertEquals($email, $user->getEmail());
    }

    public function testWhenRolesAreRemoved_ThenUserRoleIsReturnedAnyway(): void
    {
        $user = new User();
        $user->setRoles([]);
        self::assertContains("ROLE_USER", $user->getRoles());
    }

    public function testWhenIdentifierIsGet_ThenIdentifierEqualsEmail(): void
    {
        $user = new User();
        $email = "test.me@example.com";
        $user->setEmail($email);
        self::assertEquals($email, $user->getUserIdentifier());
    }

    public function testWhenPasswordIsSet_ThenCorrectPasswordIsGet(): void
    {
        $user = new User();
        $password = "thisShouldBeHashed";
        $user->setPassword($password);
        self::assertEquals($password, $user->getPassword());
    }
}
