<?php

namespace App\Tests\Controller\ProductController;

use App\Entity\Product;
use App\Entity\ProductUnitEnum;
use App\Entity\ShoppingList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateTest extends WebTestCase
{
    const URL = '/list/1/product/1/edit';

    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function testWhenUserWantsToEditProduct_ThenUserHasToBeAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToEditProduct_ThenFormIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('h1', 'Edit your product');
        self::assertSelectorExists('form');
        self::assertSelectorExists('input[name="product[name]"]');
        self::assertSelectorExists('input[name="product[quantity]"]');
        self::assertSelectorExists('select[name="product[unit]"]');
        self::assertSelectorExists('select[name="product[shoppingList]"]');

        self::assertSelectorExists('button[type="submit"]');

        self::assertSelectorTextContains('a[href="/list/1"]', 'Cancel');
    }

    public function testWhenAuthenticatedUserWantsToEditProductOnOthersList_ThenForbiddenErrorIsThrown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $user2 = $this->getUser($client, 'john.doe@example.com');
        $client->loginUser($user);
        /** @var ShoppingList $othersList */
        $othersList = $user2->getShoppingLists()[0];
        /** @var Product $othersProduct */
        $othersProduct = $othersList->getProducts()[0];

        // Prevent PHPUnit to catch exceptions with KernelBrowser
        $client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);

        $client->request('GET', '/list/' . ($othersList->getId() ?? 0) .
            '/product/' . ($othersProduct->getId() ?? 0) . '/edit');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testWhenAuthenticatedUserSubmitsForm_ThenProductIsUpdated(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $productName = 'Product name ' . uniqid();

        $client->request('GET', '/list/1');
        self::assertAnySelectorTextNotContains('.card-title', $productName);

        $client->request('GET', self::URL);

        $client->submitForm('Save', [
            'product[name]' => $productName,
            'product[quantity]' => 1,
            'product[unit]' => ProductUnitEnum::PKG->value,

        ]);

        self::assertResponseRedirects('/list/1');

        $client->request('GET', '/list/1');
        self::assertAnySelectorTextContains('.card-title', $productName);
    }

    public function testWhenAuthorizedUserClicksOnLink_ThenLinkIsNotDummy(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);

        self::assertSelectorNotExists('a[href="#"]:not([role="button"])');
    }
}
