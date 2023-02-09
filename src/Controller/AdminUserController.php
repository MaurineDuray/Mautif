<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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


    #[Route('admin/user/{slug}/edit', name:'account_profile')]
    public function userEdit(User $user, EntityManagerInterface $manager, Request $request):Response
    {
        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            
            if(!empty($file))
            {
                $originalFilename = pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin;Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename."-".uniqid().".".$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                }catch(FileException $e)
                {
                    return $e->getMessage();
                }
                $user->setAvatar($newFilename);
            }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Votre compte a bien été créé"
            );

            return $this->redirectToRoute('account_login');

        }

        return $this->render("account/editProfile.html.twig",[
            'myform' => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer un pattern à partir de l'administration (liste des motifs)
     */
    #[Route('/adminuser/{slug}/delete', name:"admin_user_delete")]
    #[IsGranted('ROLE_ADMIN')]
    public function userAdminDelete(User $user, EntityManagerInterface $manager)
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
