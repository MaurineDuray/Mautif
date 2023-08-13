<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegacyController extends AbstractController
{
    /**
     * Permet d'afficher les mentions légales
     *
     * @return Response
     */
    #[Route('/legacy', name: 'legacy')]
    public function index(): Response
    {
        return $this->render('legacy/legacy.html.twig', [
            
        ]);
    }

    /**
     * Permet d'afficher les conditions générales
     *
     * @return Response
     */
    #[Route('/conditions', name: 'conditions')]
    public function condition():Response
    {
        return $this->render('legacy/conditions.html.twig', [
            
        ]);
    }
}
