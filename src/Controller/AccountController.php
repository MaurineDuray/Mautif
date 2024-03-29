<?php

namespace App\Controller;

use DateTime;
use App\Entity\Like;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\RegistrationType;
use App\Service\MailerService;
use Symfony\Component\Mime\Email;
use App\Repository\LikeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Renderer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class AccountController extends AbstractController
{

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    
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
     * Permet à l'utilisateur de se désinscrire du site et de supprimer ses données
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route("/unregister", name:"unregister")]
    public function unregister(Request $request, EntityManagerInterface $manager, SessionInterface $session):Response
    {
        $user = $this->getUser();

        if($user){
            if($user->getAvatar()){
                unlink($this->getParameter('uploads_directory').'/'.$user->getAvatar());
            }
    
            if ($user->getComments()) {
                $comments = $user->getComments();
                 foreach ($comments as $comment) {
                     $manager->remove($comment);
                     $manager->flush();
                 }
             }
    
            //suppression des motifs et de leurs images liées à cet utilisateur
            $patterns= $user->getPatterns();
            if($patterns){
                foreach($patterns as $pattern){
                    unlink($this->getParameter('uploads_directory').'/'.$pattern->getCover());
                    $images = $pattern->getGaleries();
                    if($images){
                    foreach($images as $image){
                    unlink($this->getParameter('uploads_directory').'/'.$image->getPicture());
    
                    $manager->remove($image);
                    $manager->flush();
                }
            } 
    
                    $manager->remove($pattern);
                    $manager->flush();
                }
            }

            $manager->remove($user);
            $manager->flush();

            $this->tokenStorage->setToken(null);
            $session->invalidate();

            $this->addFlash('success','Votre compte a été désinscrit avec succès');

            return $this->redirectToRoute('homepage');
        }

        return $this->redirectToRoute('account_login');
    }

    /**
     * Afficher les likes de l'utilisateur connecté
     *
     * @return Response
     */
    #[Route("/profile/likes", name:"user_likes")]
    #[Security("(is_granted('ROLE_USER')) or is_granted('ROLE_ADMIN')", message:"Ce profil ne vous appartient pas, vous ne pouvez pas y accéder")]
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
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher, MailerInterface $mailer): Response
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
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
            $manager->flush();

            // mail send
            $email = (new TemplatedEmail())
            ->from('design@maurine.be')
            ->to($user->getEmail())
            ->subject('Validation d\'inscription')
            ->htmlTemplate('mails/registration_confirmation.html.twig')
            ->context([
                'user'=>$user
            ]);

            $mailer->send($email);


            $this->addFlash(
                'success',
                "Votre compte a bien été créé, vérifier vos emails pour l'activer"
            );

            return $this->redirectToRoute('account_login');

        }

        return $this->render("account/registration.html.twig",[
            'myform' => $form->createView()
        ]);


    }

    /**
     * Permet de vérifier le compte d'un utilistauer à l'inscription
     */
    #[Route('/verify/{id<\d+>}', name:'account_verify', methods: ['GET'])]
    public function verify( User $user, EntityManagerInterface $em)
    {

       $user->setIsVerified(true);
       
       $em->flush();

       $this->addFlash('success','Votre compte a été vérifié avec succès, vous pouvez vous connecter');
       return $this->redirectToRoute('account_login');
    }

    /**
     * Route qui permet d'afficher la page de la confirmation de désinscription du site
     *
     * @return Response
     */
    #[Route('user/confirm/{id}', name:'confirm_unsub')]
    #[Security("(is_granted('ROLE_USER')) or is_granted('ROLE_ADMIN')", message:"Ce profil ne vous appartient pas, vous ne pouvez pas y accéder")]
    public function confirm_unsub( User $user, UserRepository $repo):Response
    {

        return $this->render("account/unsub.html.twig",[
           'user'=>$this->getUser()
        ]);
    }

    
}
