<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * Permet d'afficher la liste des users inscrits au site
     *
     * @param UserRepository $repo
     * @return Response
     */
    #[Route('/admin/user', name: 'admin_user')]
    #[IsGranted("ROLE_ADMIN")]
    public function index(UserRepository $repo): Response
    {
        $users = $repo ->findAll();

        return $this->render('admin/user/adminUser.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Permet de supprimer un pattern à partir de l'administration (liste des motifs)
     */
    #[Route('/adminuser/{slug}/delete', name:"admin_user_delete")]
    #[IsGranted('ROLE_ADMIN')]
    public function patternAdminDelete(User $user, EntityManagerInterface $manager)
    {
        $this->addFlash(
            "success",
            "Le motif {$user->getId()} a bien été supprimé"
        );

        unlink($this->getParameter('uploads_directory').'/'.$user->getAvatar());
            
        $manager->remove($user);
        $manager->flush();

        return $this->redirectToRoute("admin_user");
    }
}
