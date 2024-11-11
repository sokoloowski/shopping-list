<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OverviewController extends AbstractController
{
    #[Route('/', name: 'app_overview')]
    public function overview(): Response
    {
        return $this->render('overview/overview.html.twig');
    }
}
