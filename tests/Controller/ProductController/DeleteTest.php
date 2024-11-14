<?php

namespace App\Tests\Controller\ProductController;

use App\Entity\Product;
use App\Entity\ShoppingList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteTest extends WebTestCase
{
    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        $repository = $client->getContainer()->get(UserRepository::class);
        assert($repository instanceof UserRepository);
        $user = $repository->findOneBy(['email' => $email]);
        assert($user instanceof User);
        return $user;
    }

    public function testWhenUserWantsToDeleteProduct_ThenUserHasToBeAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', '/list/1/product/1/delete');
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToDeleteProduct_ThenFormIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/list/1/product/1/delete');
        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('h1', 'Do you really want to delete this product?');
        self::assertSelectorExists('form');
        self::assertSelectorExists('button[type="submit"]');
        self::assertSelectorTextContains('a[href="/list/1"]', 'Cancel');
    }

    public function testWhenAuthenticatedUserWantsToDeleteProductOnOthersList_ThenForbiddenErrorIsThrown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $user2 = $this->getUser($client, 'john.doe@example.com');
        $client->loginUser($user);
        /** @var ShoppingList $othersList */
        $othersList = $user2->getShoppingLists()[0];
        /** @var Product $othersProduct */
        $othersProduct = $othersList->getProducts()[0];

        $client->request('GET', '/list/' . ($othersList->getId() ?? 0) .
            '/product/' . ($othersProduct->getId() ?? 0) . '/delete');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testWhenAuthenticatedUserSubmitsForm_ThenProductIsDeleted(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $productName = 'Product 0';

        $client->request('GET', '/list/1');
        self::assertAnySelectorTextContains('.card-title', $productName);

        $client->request('GET', '/list/1/product/1/delete');
        $client->submitForm('Delete');

        self::assertResponseRedirects('/list/1');

        $client->request('GET', '/list/1');
        self::assertAnySelectorTextNotContains('.card-title', $productName);
    }
}
