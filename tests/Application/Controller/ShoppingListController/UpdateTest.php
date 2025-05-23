<?php

namespace App\Tests\Application\Controller\ShoppingListController;

use App\Entity\ShoppingList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateTest extends WebTestCase
{
    const URL = '/list/1/edit';

    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function testWhenUserWantsToEditList_ThenUserHasToBeAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToEditList_ThenResponseIsSuccessful(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();
    }

    public function testWhenAuthenticatedUserWantsToEditList_ThenResponseContainsHeading(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorTextContains('h1', 'Edit your shopping list');
    }

    public function testWhenAuthenticatedUserWantsToEditList_ThenResponseContainsForm(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('form');
    }

    public function testWhenAuthenticatedUserWantsToEditList_ThenNameInputIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('input[name="shopping_list[name]"]');
    }

    public function testWhenAuthenticatedUserWantsToEditList_ThenPurchaseDateInputIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('input[name="shopping_list[purchaseDate]"]');
    }

    public function testWhenAuthenticatedUserWantsToEditList_ThenSubmitButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testWhenAuthenticatedUserWantsToEditList_ThenCancelButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertAnySelectorTextContains('a[href="/"]', 'Cancel');
    }

    public function testWhenAuthenticatedUserWantsToUpdateOthersList_ThenForbiddenErrorIsThrown(): void
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

        $client->request('GET', '/list/' . ($othersList->getId() ?? 0) . '/edit');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testWhenAuthenticatedUserSubmitsForm_ThenListIsUpdated(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $listName = 'Shopping list name ' . uniqid();

        $client->request('GET', '/');
        self::assertAnySelectorTextNotContains('.card-header>h2', $listName);

        $client->request('GET', self::URL);

        $client->submitForm('Save', [
            'shopping_list[name]' => $listName,
            'shopping_list[purchaseDate]' => (new \DateTimeImmutable("next week"))->format('Y-m-d'),
        ]);

        self::assertResponseRedirects('/list/1');

        $client->request('GET', '/');
        self::assertAnySelectorTextContains('.card-header>h2', $listName);
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
