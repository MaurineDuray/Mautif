<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Pattern;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LikeController extends AbstractController
{
    /**
     * Permet de liker un motif 
     */
    #[Route('/like/{id}', name: 'like')]
    #[IsGranted("ROLE_USER")]
    public function like(EntityManagerInterface $manager, Pattern $pattern, LikeRepository $likeRepo, Request $request): Response
    {
        $pattern = $manager->getRepository(Pattern::class)->find($pattern);
        $user = $this->getUser();

        $like = new Like();

        $like->setUser($user);

        $pattern->addLike($like);
        $manager->persist($pattern);
        $manager->flush();

        $this->addFlash(
            "success",
            "Vous avez liké le motif :{$pattern->getTitle()}"
        );

        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer);
         
    }

    /**
     * Permet de retirer le like d'un motif
     */
    #[Route('/unlike/{id}', name: 'unlike')]
    #[IsGranted("ROLE_USER")]
    public function unlike(EntityManagerInterface $manager, Like $like, Request $request):Response
    {
        
        $referer = $request->headers->get('referer');

        $this->addFlash(
            "success",
            "Vous avez retiré votre like du motif {$like->getPattern()->getTitle()}"
        );
        
        $manager->remove($like);
        $manager->flush();

        return new RedirectResponse($referer);
    }
}