<?php

namespace App\Controller;

use App\Entity\Pattern;
use App\Repository\PatternRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PatternController extends AbstractController
{
    #[Route('/patterns', name: 'patterns_index')]
    public function index(PatternRepository $repo): Response
    {
        $patterns=$repo->findAll();

        return $this->render('pattern/index.html.twig', [
            'patterns' => $patterns,
        ]);
    }

    /**
     * Permet d'afficher un motif 
     */
    #[Route('/pattern/show', name:'pattern_show')]
    public function show():Response
    {
        return $this->render('pattern/show.html.twig', [
            'pattern' => 'pattern',
        ]);
    }
}
