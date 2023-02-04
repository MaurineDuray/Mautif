<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PatternRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * Permet d'afficher le profil d'un utilisateur et le compte de use
     */
    #[Route('/user/{slug}', name: 'user_profile')]
    public function index(User $user, PatternRepository $repo): Response
    {
        $patterns=$repo->findBy(
            array(), array('id'=>'DESC')
        );

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'patterns'=>$patterns
        ]);
    }

    
}
