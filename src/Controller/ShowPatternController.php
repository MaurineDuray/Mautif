<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Pattern;
use App\Repository\PatternRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ShowPatternController extends AbstractController
{
   
    /**
     * Permet d'afficher les patterns de l'utilisateur connecté
     *
     * @param PatternRepository $repo
     * @return Response
     */
    #[Route('/patterns/{slug}/galery', name:"user_patterns")]
    #[IsGranted('ROLE_USER')]
    public function userPattern(User $user, PatternRepository $repo, string $slug):Response
    {
        $patterns = $repo->findAll();

        return $this->render('pattern/userPattern.html.twig', [
            'patterns'=> $patterns,
            'user'=>$user
        ]);
    }

}
