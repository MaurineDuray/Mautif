<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\RegistrationType;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountController extends AbstractController
{
    /**
     * Route dédiée à la connexion de l'utilisateur
     *
     * @param AuthenticationUtils $utils
     * @return Response
     */
    #[Route('/login', name: 'account_login')]
    public function index(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        return $this->render('account/index.html.twig', [
            'hasError' => $error != null,
            'username' => $username
        ]);
    }

    /**
     * Permet la déconnexion de l'utilisateur
     *
     * @return void
     */
    #[Route("/logout", name:"account_logout")]
    public function logout():void
    {
        //
    }

    /**
     * Afficher les likes de l'utilisateur connecté
     *
     * @return Response
     */
    #[Route("/profile/likes", name:"user_likes")]
    public function myLikes():Response
    {
        return $this->render('user/likes.html.twig',[
            'user'=>$this->getUser()
        ]);
    }

    
    /**
     * Permet d'afficher le formulaire d'inscription d'un utilisateur et de l'ajouter à la base de données
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route("/register", name:"account_register")]
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // gestion de mon image
            $file = $form['avatar']->getData();
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

            $hash = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Votre compte a bien été créé"
            );

            return $this->redirectToRoute('account_login');

        }

        return $this->render("account/registration.html.twig",[
            'myform' => $form->createView()
        ]);


    }

    #[Route('/user/{slug}/delete', name:"unsub")]
    public function userAdminDelete(User $user, EntityManagerInterface $manager)
    {
        $this->addFlash(
            "success",
            "Le motif {$user->getId()} a bien été supprimé"
        );

        unlink($this->getParameter('uploads_directory').'/'.$user->getAvatar());
            
        $manager->remove($user);
        $manager->flush();

        return $this->redirectToRoute("homepage");
    }

}
