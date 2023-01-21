<?php

namespace App\Controller;

use App\Repository\PatternRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PatternController extends AbstractController
{
    #[Route('/pattern', name: 'pattern_index')]
    public function index(PatternRepository $repo): Response
    {
        $patterns=$repo->findAll();

        return $this->render('pattern/index.html.twig', [
            'patterns' => $patterns,
        ]);
    }
}
