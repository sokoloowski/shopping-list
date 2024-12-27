<?php

namespace App\Tests\Application\Controller\ProductController;

use App\Entity\Product;
use App\Entity\ShoppingList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DeleteTest extends WebTestCase
{
    const URL = '/list/1/product/1/delete';

    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function testWhenUserWantsToDeleteProduct_ThenUserHasToBeAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToDeleteProduct_ThenResponseIsSuccessful(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();
    }

    public function testWhenAuthenticatedUserWantsToDeleteProduct_ThenResponseContainsHeading(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorTextContains('h1', 'Do you really want to delete this product?');
    }

    public function testWhenAuthenticatedUserWantsToDeleteProduct_ThenResponseContainsForm(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('form');
    }

    public function testWhenAuthenticatedUserWantsToDeleteProduct_ThenSubmitButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testWhenAuthenticatedUserWantsToDeleteProduct_ThenCancelButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
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

        // Prevent PHPUnit to catch exceptions with KernelBrowser
        // https://stackoverflow.com/a/50465691
        $client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);

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

        $client->request('GET', self::URL);
        $client->submitForm('Delete');

        self::assertResponseRedirects('/list/1');

        $client->request('GET', '/list/1');
        self::assertAnySelectorTextNotContains('.card-title', $productName);
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
