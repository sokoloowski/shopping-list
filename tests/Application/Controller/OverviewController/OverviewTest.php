<?php

namespace App\Tests\Application\Controller\OverviewController;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OverviewTest extends WebTestCase
{
    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function testWhenUserWantsToSeeList_ThenUserHasToLogInFirst(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthorizedUserWantsToSeeList_ThenOverviewPageExists(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
    }

    public function testWhenAuthorizedUserWantsToSeeList_ThenAllListsAreShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/');

        self::assertSelectorTextContains('h1', $user->getUsername() ?? '');
        self::assertSelectorCount(count($user->getShoppingLists()), '.card');
        self::assertSelectorTextContains('.card', 'Realisation date');
        self::assertSelectorCount(count($user->getShoppingLists()), '.card .btn');
    }

    public function testWhenAuthorizedUserWantsToModifyAccount_ThenSettingsLinkIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/');

        self::assertSelectorTextContains('a[href="/settings"]', 'Settings');
    }

    public function testWhenAuthorizedUserWantsToSeeList_ThenOverviewLinkIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/');

        self::assertAnySelectorTextContains('a[href="/"]', 'Overview');
    }

    public function testWhenAuthorizedUserClicksOnLink_ThenLinkIsNotDummy(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/');

        self::assertSelectorNotExists('a[href="#"]:not([role="button"])');
    }
}
