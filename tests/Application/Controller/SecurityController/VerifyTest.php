<?php

namespace App\Tests\Application\Controller\SecurityController;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VerifyTest extends WebTestCase
{
    private function getUser(KernelBrowser $client, string $email = 'jan.kowalski@example.com'): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function testWhenUserClicksVerificationLink_ThenUserIsVerified(): void
    {
        $email = 'john.doe@example.com';
        $client = self::createClient();

        $user = $this->getUser($client, $email);
        self::assertNull($user->getVerifiedAt());

        $client->loginUser($user);
        $client->request('GET', '/verify/' . $user->getVerificationCode());
        self::assertResponseRedirects('/login');

        $user = $this->getUser($client, $email);
        self::assertNotNull($user->getVerifiedAt());
    }

    public function testWhenUserClicksOldVerificationLink_ThenUserIsNotVerified(): void
    {
        $email = 'john.doe@example.com';
        $client = self::createClient();

        $user = $this->getUser($client, $email);
        self::assertNull($user->getVerifiedAt());

        $client->loginUser($user);
        $client->request('GET', '/verify/oldverifcode');
        self::assertResponseRedirects('/login');

        $client->followRedirect();

        self::assertSelectorExists('.alert-danger');
        self::assertSelectorTextContains('.alert-danger', 'Invalid verification code');

        $user = $this->getUser($client, $email);
        self::assertNull($user->getVerifiedAt());
    }
}
