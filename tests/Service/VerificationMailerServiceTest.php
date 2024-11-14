<?php

namespace App\Tests\Service;

use App\Service\VerificationMailerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VerificationMailerServiceTest extends TestCase
{
    public function testWhenMailerInterfaceThrowsException_ThenServiceThrowsExceptionToo(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException());
        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->method('generate')->willReturn('http://example.com');

        $service = new VerificationMailerService($router, $mailer);

        $this->expectException(TransportExceptionInterface::class);
        $service->send('test@example.com', '123456');
    }
}
