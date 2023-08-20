<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Pattern;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    /**
     * Permet de supprimer un commentaire
     */
    #[Route('/comment/{id}/delete', name:"delete_comment")]
    #[Security("(is_granted('ROLE_USER') and user === comment.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Ce commentaire ne vous appartient pas, vous ne pouvez pas le supprimer")]
    public function deleteComment(Comment $comment, EntityManagerInterface $manager):Response
    {
        $this->addFlash(
            'success',
            "Le commentaire a été supprimé"
        );
        $pattern = $comment->getIdPattern()->getSlug();

        $manager->remove($comment);
        $manager->flush();

        return $this->redirectToRoute('pattern_show',[
            'slug'=>$pattern
        ]);
    }
}
