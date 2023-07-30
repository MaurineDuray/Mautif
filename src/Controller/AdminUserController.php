<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\AvatarModify;
use App\Entity\PasswordUpdate;
use App\Form\AvatarModifyType;
use App\Form\PasswordUpdateType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
     * Permet d'éditer l'image avatar d'un user
     */
    #[Route('admin/user/{slug}/avataredit', name:"avatar_edit")]
    public function imgEdit(User $user, EntityManagerInterface $manager, Request $request):Response
    {
        $imgModify = new AvatarModify();
        
        $form = $this->createForm(AvatarModifyType::class, $imgModify);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // supprimer l'image dans le dossier
            if(!empty($user->getAvatar()))
            {
                unlink($this->getParameter('uploads_directory').'/'.$user->getAvatar());
            }

            $file = $form['newAvatar']->getData();
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
                'Votre avatar a bien été modifié'
            );

            return $this->redirectToRoute('user_profile',['slug'=>$user->getSlug()]);

        }

        return $this->render("account/avataredit.html.twig",[
            'myform' => $form->createView()
        ]);
    }

    /**
     * Permet d'éditer les info d'un user
     */
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

            return $this->redirectToRoute('user_profile',['slug'=>$user->getSlug()]);

        }

        return $this->render("account/editProfile.html.twig",[
            'myform' => $form->createView()
        ]);
    }

     /**
     * Permet de changer de mot de passe 
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route("/account/password-update", name:'account_password')]
    public function updatePassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // vérifier que le mot de passe correspond à l'ancien
            if(!password_verify($passwordUpdate->getOldPassword(),$user->getPassword()))
            {
                // gérer l'erreur
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé n'est pas votre mot de passe actuel"));
            }else{
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $hasher->hashPassword($user, $newPassword);

                $user->setPassword($hash);
                $manager->persist($user);
                $manager->flush();

                $slug= $user->getSlug();

                $this->addFlash(
                    'success',
                    "Votre mot de passe a bien été modifié"
                );

                return $this->redirectToRoute('user_profile',[
                    'slug'=> $slug
                ]);
            }
        }


        return $this->render("account/password.html.twig",[
            'myform' => $form->createView()
        ]);
    }

    /**
     * PErmet à l'administrateur de changer le rôle d'un user en admin à partir de l'administration
     */
    #[Route('/adminuser/{slug}/changeadmin', name:"role_change")]
    #[IsGranted('ROLE_ADMIN')]
    public function adminChange(User $user, EntityManagerInterface $manager)
    {

        $this->addFlash(
            "success",
            "Le rôle de {$user->getPseudo()} a bien été changé en ADMIN"
        );

       
            $user->setRoles(["ROLE_ADMIN"]);
            $manager->persist($user);
            $manager->flush();
        

        return $this->redirectToRoute("admin_user");
    }

    /**
     * PErmet à l'administrateur de changer le rôle d'un admin en user à partir de l'administration
     */
    #[Route('/adminuser/{slug}/changeuser', name:"user_change")]
    #[IsGranted('ROLE_ADMIN')]
    public function userChange(User $user, EntityManagerInterface $manager)
    {
        $this->addFlash(
            "success",
            "Le rôle de {$user->getPseudo()} a bien été changé en USER"
        );

            $user->setRoles(["ROLE_USER"]);
            $manager->persist($user);
            $manager->flush();
        

        return $this->redirectToRoute("admin_user");
    }

    /**
     * Permet de supprimer un user à partir de l'administration (liste des motifs)
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

    /**
     * Permet d'envoi d'un code par un admin pour la récupération de mot de passe 
     */
    #[Route('/usernewmdp/{slug}', name:"new_mdp")]
    public function newmdp(User $user, EntityManagerInterface $manager, MailerInterface $mailer, UserPasswordHasherInterface $hasher):Response
    {
            $password=random_int(100, 999999);
            
            // mail send
            $email = (new TemplatedEmail())
            ->from('design@maurine.be')
            ->to($user->getEmail())
            ->subject('Changement de mot de passe')
            ->htmlTemplate('mails/password.html.twig')
            ->context([
                'user'=>$user,
                'password'=>$password
            ]);

            $mailer->send($email);

            $this->addFlash(
                "success",
                "Le nouveau mot de passe a bien été envoyé !"
            );


            $newPassword = $password;
            $hash = $hasher->hashPassword($user, $newPassword);

            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();

            $slug= $user->getSlug();

            return $this->redirectToRoute('user_profile',[
                'slug'=> $slug
            ]);
        
    }
}
