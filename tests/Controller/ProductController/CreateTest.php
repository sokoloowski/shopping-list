<?php

namespace App\Tests\Controller\ProductController;

use App\Entity\ProductUnitEnum;
use App\Entity\ShoppingList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateTest extends WebTestCase
{
    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        $repository = $client->getContainer()->get(UserRepository::class);
        assert($repository instanceof UserRepository);
        $user = $repository->findOneBy(['email' => $email]);
        assert($user instanceof User);
        return $user;
    }

    public function testWhenUserWantsToCreateProduct_ThenUserHasToBeAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', '/list/1/product/new');
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenFormIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/list/1/product/new');
        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('h1', 'Add a new product to');
        self::assertSelectorExists('form');
        self::assertSelectorExists('input[name="product[name]"]');
        self::assertSelectorExists('input[name="product[quantity]"]');
        self::assertSelectorExists('select[name="product[unit]"]');

        self::assertSelectorNotExists('select[name="product[shoppingList]"]');

        self::assertSelectorExists('button[type="submit"]');

        self::assertSelectorTextContains('a[href="/list/1"]', 'Cancel');
    }

    public function testWhenAuthenticatedUserWantsToCreateProductOnOthersList_ThenForbiddenErrorIsThrown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $user2 = $this->getUser($client, 'john.doe@example.com');
        $client->loginUser($user);
        /** @var ShoppingList $othersList */
        $othersList = $user2->getShoppingLists()[0];

        $client->request('GET', '/list/' . ($othersList->getId() ?? 0) . '/product/new');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testWhenAuthenticatedUserSubmitsForm_ThenProductIsCreated(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $productName = 'Product name ' . uniqid();

        $client->request('GET', '/list/1');
        self::assertAnySelectorTextNotContains('.card-title', $productName);

        $client->request('GET', '/list/1/product/new');

        $client->submitForm('Save', [
            'product[name]' => $productName,
            'product[quantity]' => 1,
            'product[unit]' => ProductUnitEnum::PKG->value,
        ]);

        self::assertResponseRedirects('/list/1');

        $client->request('GET', '/list/1');
        self::assertAnySelectorTextContains('.card-title', $productName);
    }
}
