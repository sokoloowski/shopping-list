<?php

namespace App\Tests\Application\Controller\ProductController;

use App\Entity\ProductUnitEnum;
use App\Entity\ShoppingList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreateTest extends WebTestCase
{
    const URL = '/list/1/product/new';

    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function testWhenUserWantsToCreateProduct_ThenUserHasToBeAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenResponseIsSuccessful(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenResponseContainsHeading(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorTextContains('h1', 'Add a new product to');
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenResponseContainsForm(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('form');
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenAutocompleteButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('button[onclick]');
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenNameInputIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('input[name="product[name]"]');
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenQuantityInputIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('input[name="product[quantity]"]');
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenUnitSelectIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('select[name="product[unit]"]');
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenListSelectIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorNotExists('select[name="product[shoppingList]"]');
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenSubmitButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testWhenAuthenticatedUserWantsToCreateProduct_ThenCancelButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
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

        // Prevent PHPUnit to catch exceptions with KernelBrowser
        // https://stackoverflow.com/a/50465691
        $client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);

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
