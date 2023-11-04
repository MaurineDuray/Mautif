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
     * Route qui permet de liker et unliker un motif
     */
    #[Route('/like/{id}/{action}', name: 'like')]
    #[IsGranted("ROLE_USER")]
    public function like(EntityManagerInterface $manager,Pattern $pattern, Request $request,$action, LikeRepository $likeRepo): Response {
        $user = $this->getUser();
    
        // Vérifiez si l'utilisateur a déjà aimé ce motif
        $existingLike = $likeRepo->findOneBy(['user' => $user, 'pattern' => $pattern]);
    
        if ($action === "add") {
            if (!$existingLike) {
                $like = new Like();
                $like->setUser($user);
                $pattern->addLike($like);
                $manager->persist($pattern);
                $manager->flush();
    
               
                return $this->json(['message'=>'Le like a été ajouté']);
            }
        } elseif ($action === "remove") {
            if ($existingLike) {
                $manager->remove($existingLike);
                $manager->flush();
    
               
                return $this->json(['message'=>'Le like a été supprimé']);
            } 
        }
    
        // $referer = $request->headers->get('referer');
    
        // return new RedirectResponse($referer);

        return new Response();
        }


    /**Ancienne version avant évolution */
    // /**
    //  * Permet de retirer le like d'un motif
    //  */
    // #[Route('/unlike/{id}', name: 'unlike')]
    // #[IsGranted("ROLE_USER")]
    // public function unlike(EntityManagerInterface $manager, Like $like, Request $request):Response
    // {
        
    //     $referer = $request->headers->get('referer');

    //     $this->addFlash(
    //         "success",
    //         "Vous avez retiré votre like du motif {$like->getPattern()->getTitle()}"
    //     );
        
    //     $manager->remove($like);
    //     $manager->flush();

    //     return new RedirectResponse($referer);
    // }
}
