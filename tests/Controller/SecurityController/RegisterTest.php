<?php

namespace App\Tests\Controller\SecurityController;

use App\Repository\UserRepository;
use App\Service\VerificationMailerService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\Exception\TransportException;

class RegisterTest extends WebTestCase
{
    const URL = '/register';

    public function testWhenClientWantsToRegister_ThenRegisterPageExists(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();
    }

    public function testWhenOnRegisterPage_ThenEmailInputIsShown(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertSelectorExists('input[type=email]');
    }

    public function testWhenOnRegisterPage_ThenPasswordInputIsShown(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertSelectorCount(2, 'input[type=password]');
    }

    public function testWhenOnRegisterPage_ThenSubmitButtonIsShown(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertSelectorExists('[type=submit]');
    }

    public function testWhenOnRegisterPage_ThenLoginButtonIsShown(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);
        self::assertSelectorExists('a[href="/login"]');
    }

    public function testWhenUserTriesToSignUp_ThenPasswordMustBeConfirmed(): void
    {
        $password = '$tr0ngP4$$w0rd';
        $client = self::createClient();
        $client->request('GET', self::URL);
        $client->submitForm('Sign up', [
            'register[email]' => 'test@example.com',
            'register[password][first]' => $password,
            'register[password][second]' => $password . '_incorrect',
            'register[username]' => 'test'
        ]);
        self::assertSelectorExists('.invalid-feedback');
        self::assertSelectorTextContains('.invalid-feedback', 'must match');
    }

    public function testWhenUserSignsUp_ThenAccountIsCreated(): void
    {
        $password = '$tr0ngP4$$w0rd';
        $client = self::createClient();
        $client->request('GET', self::URL);
        $client->submitForm('Sign up', [
            'register[email]' => 'test@example.com',
            'register[password][first]' => $password,
            'register[password][second]' => $password,
            'register[username]' => 'test'
        ]);
        self::assertSelectorNotExists('.invalid-feedback');
        self::assertResponseRedirects('/login');

        $client->followRedirect();

        self::assertSelectorExists('.alert-success');
        self::assertSelectorTextContains('.alert-success', 'Check Your inbox for verification link');
    }

    public function testWhenUserSignsUp_ThenPasswordIsHashed(): void
    {
        $client = self::createClient();
        $email = 'test@example.com';
        $password = '$tr0ngP4$$w0rd';

        $client->request('GET', self::URL);
        $client->submitForm('Sign up', [
            'register[email]' => $email,
            'register[password][first]' => $password,
            'register[password][second]' => $password,
            'register[username]' => 'test'
        ]);

        $repository = $client->getContainer()->get(UserRepository::class);
        self::assertInstanceOf(UserRepository::class, $repository);
        $user = $repository->findOneBy(['email' => $email]);
        self::assertNotNull($user);
        self::assertNotEquals($password, $user->getPassword());
    }

    public function testWhenUserSignsUp_ThenVerificationMailIsSent(): void
    {
        $client = self::createClient();
        $email = 'test@example.com';
        $password = '$tr0ngP4$$w0rd';

        $client->request('GET', self::URL);
        $client->submitForm('Sign up', [
            'register[email]' => $email,
            'register[password][first]' => $password,
            'register[password][second]' => $password,
            'register[username]' => 'test'
        ]);

        self::assertEmailCount(1);

        $message = self::getMailerMessage();

        self::assertNotNull($message);
        self::assertEmailHeaderSame($message, 'To', $email);
        self::assertEmailHtmlBodyContains($message, '/verify');
    }

    public function testWhenUserSignsUpAndErrorIsThrown_ThenUserIsRemoved(): void
    {
        self::markTestIncomplete('Mock is not mocking');
        $client = self::createClient(); // @phpstan-ignore-line I know it doesn't work
        $email = 'test@example.com';
        $password = '$tr0ngP4$$w0rd';

        // FIXME: mock is not working
        $mock = $this->createMock(VerificationMailerService::class);
        $mock->method('send')
            ->willThrowException(new TransportException());
        $client->getContainer()->set(VerificationMailerService::class, $mock);
        $client->request('GET', self::URL);

        $this->expectException(TransportException::class);
        $client->submitForm('Sign up', [
            'register[email]' => $email,
            'register[password][first]' => $password,
            'register[password][second]' => $password,
            'register[username]' => 'test'
        ]);

        $client->followRedirect();

        self::assertSelectorExists('.alert-danger');
        self::assertSelectorTextContains('.alert-danger','Could not send verification e-mail');
    }

    public function testWhenAuthorizedUserClicksOnLink_ThenLinkIsNotDummy(): void
    {
        $client = self::createClient();
        $client->request('GET', self::URL);

        self::assertSelectorNotExists('a[href="#"]:not([role="button"])');
    }
}
