<?php

namespace App\Tests\Application\Controller\ShoppingListController;

use App\Entity\User;
use App\Repository\ShoppingListRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateTest extends WebTestCase
{
    const URL = '/list/new';

    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function testWhenUserWantsToCreateList_ThenUserHasToBeAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToCreateList_ThenResponseIsSuccessful(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();
    }

    public function testWhenAuthenticatedUserWantsToCreateList_ThenResponseContainsHeading(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorTextContains('h1', 'Create new shopping list');
    }

    public function testWhenAuthenticatedUserWantsToCreateList_ThenResponseContainsForm(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('form');
    }

    public function testWhenAuthenticatedUserWantsToCreateList_ThenNameInputIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('input[name="shopping_list[name]"]');
    }

    public function testWhenAuthenticatedUserWantsToCreateList_ThenPurchaseDateInputIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('input[name="shopping_list[purchaseDate]"]');
    }

    public function testWhenAuthenticatedUserWantsToCreateList_ThenSubmitButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testWhenAuthenticatedUserWantsToCreateList_ThenCancelButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertAnySelectorTextContains('a[href="/"]', 'Cancel');
    }

    public function testWhenAuthenticatedUserSubmitsForm_ThenListIsCreated(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $listName = 'Shopping list name ' . uniqid();

        /** @var ShoppingListRepository $repository */
        $repository = $client->getContainer()->get(ShoppingListRepository::class);
        // Get the highest ID value from the database
        $lastId = $repository->findBy([], ['id' => 'DESC'], 1)[0]->getId();

        $client->request('GET', '/');
        self::assertAnySelectorTextNotContains('.card-header>h2', $listName);

        $client->request('GET', self::URL);

        $client->submitForm('Save', [
            'shopping_list[name]' => $listName,
            'shopping_list[purchaseDate]' => (new \DateTimeImmutable("next week"))->format('Y-m-d'),
        ]);

        self::assertResponseRedirects('/list/' . ($lastId + 1));

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
