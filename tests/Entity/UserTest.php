<?php

namespace App\Tests\Entity;

use App\Entity\ShoppingList;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserTest extends TestCase
{
    public function testWhenUserVerifiedAtIsNull_ThenUserIsVerifiedIsFalse(): void
    {
        $user = new User();
        $this->assertNull($user->getVerifiedDate());
        $this->assertFalse($user->isVerified());
    }

    public function testWhenUserIsRegistered_ThenRegisteredAtIsNotNull(): void
    {
        $dateBeforeRegister = new \DateTimeImmutable();
        $user = new User();
        $this->assertNotNull($user->getRegisteredAt());
        $this->assertGreaterThan($dateBeforeRegister, $user->getRegisteredAt());
        $this->assertLessThan(new \DateTimeImmutable(), $user->getRegisteredAt());
    }

    public function testWhenUserIsRegistered_ThenVerificationCodeIsNotNull(): void
    {
        $user = new User();
        $this->assertNotNull($user->getVerificationCode());
        $this->assertNotEmpty($user->getVerificationCode());
    }

    public function testWhenWrongVerificationCodeUsed_ThenHttpExceptionIsThrown(): void
    {
        $user = new User();
        $this->expectException(HttpException::class);
        $user->verify($user->getVerificationCode() . "_incorrect");
    }

    public function testWhenCorrectVerificationCodeUsed_ThenVerificationDateIsSet(): void
    {
        $dateBeforeVerification = new \DateTimeImmutable();
        $user = new User();
        $user->verify($user->getVerificationCode());
        $this->assertNotNull($user->getVerifiedAt());
        $this->assertGreaterThan($dateBeforeVerification, $user->getVerifiedAt());
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
        $list->expects($this->once())
            ->method("setOwner")
            ->with($user);
        $user->addShoppingList($list);
        $this->assertContains($list, $user->getShoppingLists());
    }
}
