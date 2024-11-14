<?php

namespace App\Tests\Controller\ProductController;

use App\Entity\Product;
use App\Entity\ShoppingList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PhotoTest extends WebTestCase
{
    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function testWhenUserWantsToToggleProduct_ThenUserHasToBeAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', '/list/1/product/1/photo');
        self::assertResponseRedirects('/login');
    }

    public function testWhenAuthenticatedUserWantsToFetchOthersProductPhoto_ThenForbiddenErrorIsThrown(): void
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
            '/product/' . ($othersProduct->getId() ?? 0) . '/photo');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testWhenAuthenticatedUserWantsToFetchProductPhoto_ThenResponseIsAnImage(): void
    {
        $client = self::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/list/1/product/1/photo');

        $contentType = $client->getResponse()->headers->get('Content-Type');
        self::assertStringContainsString('image/', $contentType ?? '');
    }
}
