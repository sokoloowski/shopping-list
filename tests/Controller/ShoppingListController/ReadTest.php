<?php

namespace App\Tests\Controller\ShoppingListController;

use App\Entity\ShoppingList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ReadTest extends WebTestCase
{
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
        $client->request('GET', '/list/1');
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

        $client->request('GET', '/list/' . ($othersList->getId() ?? 0));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testWhenAuthenticatedUserWantsToDisplayList_ThenListIsShown(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/list/1');
        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('.col>.card .btn-success', 'In your cart');
        self::assertSelectorTextContains('.col>.card .btn-outline-success', 'Collect');
        self::assertSelectorTextContains('.col>.card .btn-primary', 'Edit');
        self::assertSelectorTextContains('.col>.card .btn-danger', 'Remove');

        self::assertSelectorTextContains('h1~div>.btn-primary', 'Edit');
        self::assertSelectorTextContains('h1~div>.btn-danger', 'Delete');
    }
}
