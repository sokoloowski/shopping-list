<?php

namespace App\Tests\Application\Controller\ShoppingListController;

use App\Entity\ShoppingList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ReadTest extends WebTestCase
{
    const URL = '/list/1';

    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function testWhenUserWantsToDisplayList_ThenUserHasToBeAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToDisplayProductsOnOthersList_ThenForbiddenErrorIsThrown(): void
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

        $client->request('GET', '/list/' . ($othersList->getId() ?? 0));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testWhenAuthenticatedUserWantsToDisplayList_ThenResponseIsSuccessful(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();
    }

    public function testWhenAuthenticatedUserWantsToDisplayList_ThenCartButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorTextContains('.col>.card .btn-success', 'In your cart');
    }

    public function testWhenAuthenticatedUserWantsToDisplayList_ThenCollectButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorTextContains('.col>.card .btn-outline-success', 'Collect');
    }

    public function testWhenAuthenticatedUserWantsToDisplayList_ThenEditButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorTextContains('.col>.card .btn-primary', 'Edit');
    }

    public function testWhenAuthenticatedUserWantsToDisplayList_ThenRemoveButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorTextContains('.col>.card .btn-danger', 'Remove');
    }

    public function testWhenAuthenticatedUserWantsToDisplayList_ThenListEditButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorTextContains('h1~div>.btn-primary', 'Edit');
    }

    public function testWhenAuthenticatedUserWantsToDisplayList_ThenListDeleteButtonIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', self::URL);
        self::assertSelectorTextContains('h1~div>.btn-danger', 'Delete');
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
