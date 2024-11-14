<?php

namespace App\Tests\Controller\ShoppingListController;

use App\Entity\ShoppingList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteTest extends WebTestCase
{
    const URL = '/list/1/delete';

    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function testWhenUserWantsToDeleteList_ThenUserHasToBeAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToDeleteList_ThenFormIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('h1', 'Do you really want to delete this list?');
        self::assertSelectorExists('form');
        self::assertSelectorExists('button[type="submit"]');
        self::assertAnySelectorTextContains('a[href="/"]', 'Cancel');
    }

    public function testWhenAuthenticatedUserWantsToDeleteListOnOthersList_ThenForbiddenErrorIsThrown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $user2 = $this->getUser($client, 'john.doe@example.com');
        $client->loginUser($user);
        /** @var ShoppingList $othersList */
        $othersList = $user2->getShoppingLists()[0];

        $client->request('GET', '/list/' . ($othersList->getId() ?? 0) . '/delete');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testWhenAuthenticatedUserSubmitsForm_ThenListIsDeleted(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $listName = 'Shopping list 0';

        $client->request('GET', '/');
        self::assertAnySelectorTextContains('.card-header>h2', $listName);

        $client->request('GET', '/list/1');
        self::assertResponseIsSuccessful();

        $client->request('GET', self::URL);
        $client->submitForm('Delete');

        self::assertResponseRedirects('/');

        $client->request('GET', '/');
        self::assertAnySelectorTextNotContains('.card-header>h2', $listName);

        $client->request('GET', '/list/1');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
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
