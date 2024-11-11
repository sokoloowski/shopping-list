<?php

namespace App\Tests\Controller\SecurityController;

use App\Repository\UserRepository;
use App\Service\VerificationMailerService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\Exception\TransportException;

class VerifyTest extends WebTestCase
{
    public function testWhenUserClicksVerificationLink_ThenUserIsVerified(): void
    {
        $email = 'john.doe@example.com';
        $client = self::createClient();

        $repository = $client->getContainer()->get(UserRepository::class);
        self::assertInstanceOf(UserRepository::class, $repository);
        $user = $repository->findOneBy(['email' => $email]);
        self::assertNotNull($user);
        self::assertNull($user->getVerifiedAt());

        $client->loginUser($user);
        $client->request('GET', '/verify/' . $user->getVerificationCode());
        self::assertResponseRedirects('/login');

        $user = $repository->findOneBy(['email' => $email]);
        self::assertNotNull($user);
        self::assertNotNull($user->getVerifiedAt());
    }

    public function testWhenUserClicksOldVerificationLink_ThenUserIsNotVerified(): void
    {
        $email = 'john.doe@example.com';
        $client = self::createClient();

        $repository = $client->getContainer()->get(UserRepository::class);
        self::assertInstanceOf(UserRepository::class, $repository);
        $user = $repository->findOneBy(['email' => $email]);
        self::assertNotNull($user);
        self::assertNull($user->getVerifiedAt());

        $client->loginUser($user);
        $client->request('GET', '/verify/oldverifcode');
        self::assertResponseRedirects('/login');

        $client->followRedirect();

        self::assertSelectorExists('.alert-danger');
        self::assertSelectorTextContains('.alert-danger', 'Invalid verification code');

        $user = $repository->findOneBy(['email' => $email]);
        self::assertNotNull($user);
        self::assertNull($user->getVerifiedAt());
    }
}
