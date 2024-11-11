<?php

namespace App\Tests\Controller\OverviewController;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OverviewTest extends WebTestCase
{
    public function testUserWantsToSeeList_ThenUserHasToLogInFirst(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        self::assertResponseRedirects('/login');
    }
}
