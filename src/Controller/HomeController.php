<?php

namespace App\Controller;

use App\Repository\PatternRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Affiche la page d'accueil du site
     *
     * @return Response
     */
    #[Route('/', name: 'homepage')]
    public function index(PatternRepository $patternRepo, EntityManagerInterface $manager): Response
    {
        $motifs = $patternRepo->findPatternsWithMostLikes(3);

        return $this->render('home.html.twig', [
           'patterns'=>$motifs
        ]);
    }

    #[Route('/contactadmin', name:'contact_admin')]
    public function contactAdmin():Response
    {
        return $this->render('home.html.twig', [
            
        ]);
    }

}
