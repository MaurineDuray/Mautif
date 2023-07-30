<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegacyController extends AbstractController
{
    /**
     * Permet d'afficher les conditions légales
     *
     * @return Response
     */
    #[Route('/legacy', name: 'legacy')]
    public function index(): Response
    {
        return $this->render('legacy/legacy.html.twig', [
            'controller_name' => 'LegacyController',
        ]);
    }
}
