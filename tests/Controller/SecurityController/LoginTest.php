<?php

namespace App\Tests\Controller\SecurityController;

use App\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testWhenClientWantsToLogIn_ThenLoginPageExists(): void
    {
        $client = self::createClient();
        $crawler = $client->request("GET", "/login");
        self::assertResponseIsSuccessful();
    }

    public function testWhenOnLoginPage_ThenEmailInputIsShown(): void
    {
        $client = self::createClient();
        $crawler = $client->request("GET", "/login");
        self::assertSelectorExists("input[type=email]");
    }

    public function testWhenOnLoginPage_ThenPasswordInputIsShown(): void
    {
        $client = self::createClient();
        $crawler = $client->request("GET", "/login");
        self::assertSelectorExists("input[type=password]");
    }

    public function testWhenOnLoginPage_ThenSubmitButtonIsShown(): void
    {
        $client = self::createClient();
        $crawler = $client->request("GET", "/login");
        self::assertSelectorExists("[type=submit]");
    }

    public function testWhenOnLoginPage_ThenRegisterButtonIsShown(): void
    {
        $client = self::createClient();
        $crawler = $client->request("GET", "/login");
        self::assertSelectorExists("a[href='/register']");
    }

    public function testWhenUserTriesToUseWrongPassword_ThenMessageIsShown(): void
    {
        $client = self::createClient();
        $crawler = $client->request("GET", "/login");
        $client->submitForm("Sign in", [
            "_username" => "jan.kowalski@example.com",
            "_password" => "thisPasswordIsIncorrect"
        ]);
        $client->followRedirect();
        self::assertSelectorExists(".alert-danger");
        self::assertSelectorTextContains(".alert-danger", "Invalid credentials");
    }

    /**
     * @see UserFixtures
     */
    public function testWhenUserTriesToUseCorrectPassword_ThenUserIsRedirectedToHome(): void
    {
        $username = "jan.kowalski@example.com";
        $client = self::createClient();
        $crawler = $client->request("GET", "/login");
        $client->submitForm("Sign in", [
            "_username" => $username,
            "_password" => "password123"
        ]);
        self::assertResponseRedirects("/");

        # check if user is logged in
        $crawler = $client->request("GET", "/login");
        self::assertSelectorExists(".alert-warning");
        self::assertSelectorTextContains(".alert-warning", "You are logged in");
        self::assertSelectorTextContains(".alert-warning", $username);
    }

    public function testWhenUserLogsOut_ThenUserIsRedirectedToHome(): void
    {
        $client = self::createClient();
        $crawler = $client->request("GET", "/logout");
        self::assertResponseRedirects("/");

        # check if user is logged out
        $crawler = $client->request("GET", "/login");
        self::assertSelectorNotExists(".alert-warning");
    }
}
