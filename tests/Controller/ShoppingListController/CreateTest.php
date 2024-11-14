<?php

namespace App\Tests\Controller\ShoppingListController;

use App\Entity\User;
use App\Repository\ShoppingListRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateTest extends WebTestCase
{
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
        $client->request('GET', '/list/new');
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToCreateList_ThenFormIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/list/new');
        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('h1', 'Create new shopping list');
        self::assertSelectorExists('form');
        self::assertSelectorExists('input[name="shopping_list[name]"]');
        self::assertSelectorExists('input[name="shopping_list[purchaseDate]"]');

        self::assertSelectorExists('button[type="submit"]');

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

        $client->request('GET', '/list/new');

        $client->submitForm('Save', [
            'shopping_list[name]' => $listName,
            'shopping_list[purchaseDate]' => (new \DateTimeImmutable("next week"))->format('Y-m-d'),
        ]);

        self::assertResponseRedirects('/list/' . ($lastId + 1));

        $client->request('GET', '/');
        self::assertAnySelectorTextContains('.card-header>h2', $listName);
    }
}
