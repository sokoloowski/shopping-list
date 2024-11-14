<?php

namespace App\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class VerificationMailerService
{
    public function __construct(
        private UrlGeneratorInterface $router,
        private MailerInterface       $mailer
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(string $to, string $verificationCode): void
    {
        $verificationLink = $this->router->generate(
            'app_verify',
            ['code' => $verificationCode],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new Email())
            ->from('verify@shopping.sokoloowski.pl')
            ->to($to)
            ->subject('Verify your email')
            ->html('Click <a href="' . $verificationLink . '">here</a> to verify your email');

        $this->mailer->send($email);
    }
}